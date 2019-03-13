<?php

namespace SapeRt\Api\Client;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SapeRt\Api\Config;
use SapeRt\Api\Http;

use SapeRt\Api\Exception\Exception;
use SapeRt\Api\Exception\AppException;
use SapeRt\Api\Exception\ConfigException;
use SapeRt\Api\Exception\RequestException;
use SapeRt\Api\Exception\ResponseException;

abstract class Base implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array Default params for the query */
    protected $params = array();

    /** @var Http */
    protected $http;

    /** @var Config */
    protected $config;


    public function __construct(Config $config)
    {
        $this->config = $config;

        foreach ($config->getConfigurators() as $configurator) {
            $configurator($this);
        }
    }

    /* Setter & getters */

    public function getMethodName($function_name)
    {
        $method = str_replace('_', '/', $function_name);
        $method = str_replace('_', '-', self::camelCaseToUnderscore($method));

        return $method;
    }

    public function setOnline($value)
    {
        return $this->setParam('online', (int) $value);
    }

    public function getOnline()
    {
        return $this->getParam('online');
    }

    public function setJsonType($value)
    {
        return $this->setParam('json_type', $value);
    }

    public function getJsonType($default = null)
    {
        return $this->getParam('json_type', $default);
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParam($key, $default = null)
    {
        return @$this->params[$key] ?: $default;
    }

    public function getParams()
    {
        return array_filter((array) $this->params, function ($i) { return $i !== null; });
    }

    public function addParams($values)
    {
        foreach ((array) $values as $k => $v) {
            $this->setParam($k, $v);
        }

        return $this;
    }

    public function setParams($values)
    {
        $this->params = $values;
    }

    /**
     * @return string
     * @throws ConfigException
     */
    public function getBaseUrl()
    {
        if (!$namespace = $this->config->getNamespace()) {
            throw new ConfigException('Unset api namespace');
        }

        $base_url = $this->getConfig()->getBaseUrl();
        $base_url = trim($base_url, '/') . '/' . trim($namespace, '/') . '/';

        return $base_url;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Http
     */
    public function getHttpClient()
    {
        if (!$this->http) {
            $this->http = new Http([
                'timeout' => 300,
                'cookies' => true,
            ]);
        }

        return $this->http;
    }

    /* Система */

    /**
     * @param $login
     * @param $token
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-system.login
     */
    public function system_login($login, $token)
    {
        return $this->request('GET', str_replace('_', '/', __FUNCTION__), ['login' => $login, 'token' => $token]);
    }

    /* Словарь */

    /**
     * Получить словарь
     *
     * @param bool  $names_only
     * @param array $include
     * @param array $exclude
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-dictionary.dict
     */
    public function dictionary_dict($names_only = false, $include = [], $exclude = [])
    {
        $params['names-only'] = $names_only;
        $include and $params['include'] = $include;
        $exclude and $params['exclude'] = $exclude;

        return $this->request('GET', str_replace('_', '/', __FUNCTION__), $params);
    }

    /**
     * Базовый метод запроса
     *
     * @param string $method HTTP-метод
     * @param string $path   URI-Путь
     * @param array  $params Query-Параметры
     * @param array  $data   Данные запроса
     * @param array  $files  Загрузить файлы (будет использован POST)
     * @param bool   $postdata Передавать данные через POST (по-умолчанию данные передаются в виде JSON)
     *
     * @return array
     * @throws Exception
     */
    protected function request($method, $path, array $params = [], array $data = [], array $files = [], $postdata = false)
    {
        $params = array_merge($this->getParams(), $params);

        $options = [];

        $uri   = $this->getBaseUrl() . '/' . trim($path, '/') . '/';
        $label = '';
        if ($this->logger) {
            $label = md5(json_encode([$method, $uri, $options]));
        }

        if ($params) {
            $options['query'] = $params;
        }

        try {
            if ($files) {
                $options['multipart'] = [];
                foreach ($files as $key => $filename) {
                    if (!is_array($filename)) {
                        $options['multipart'][] = [
                            'name'     => $key,
                            'contents' => $this->getFileStream($filename),
                            'filename' => $filename,
                        ];
                    } else {
                        foreach ($filename as $f) {
                            $options['multipart'][] = [
                                'name'     => $key,
                                'contents' => $this->getFileStream($f),
                                'filename' => $filename,
                            ];
                        }
                    }
                }

                if ($data) {
                    foreach ($data as $key => $value) {
                        $options['multipart'][] = [
                            'name'     => $key,
                            'contents' => $value,
                        ];
                    }
                }
            } else if ($data) {
                if ($postdata) {
                    $options['form_params'] = $data;
                } else {
                    $options['json'] = $data;
                }
            }

            if ($this->logger) {
                $url = $uri . ($options['query'] ? '?' . http_build_query($options['query']) : '');
                $this->logger->info(sprintf('request[%s]: %s %s', $label, $method, $url));
            }

            /** @var ResponseInterface $response */
            $response = $this->getHttpClient()->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            $request = null;
            if ($e instanceof GuzzleRequestException) {
                $request = $e->getRequest();
            }

            if ($this->logger) {
                $this->logger->error(sprintf('error[%s]: %s %s', $label, $e->getCode(), $e->getMessage()));
            }

            throw new RequestException($request, 'Cannot perform request', $e->getCode(), $e);
        }

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();

        if ($this->logger) {
            $this->logger->info(sprintf('response[%s]: %s %s', $label, $statusCode, $body));
        }

        $json   = @json_decode($body, true);
        $status = @$json['status'];

        if (!isset($status['is_success'])) {
            $body_sample = sprintf('Body: [%s]', substr($body, 0, $this->config->getErrorBodyLength()));

            if ($statusCode < 400) {
                $message = 'Decode error. ' . $body_sample;
                $code    = $statusCode;
            } else {
                $message = 'Response error. ' . $body_sample;
                $code    = 555;
            }

            throw new ResponseException($message, $code);
        }

        if (!$status['is_success']) {
            if (isset($status['message'])) {
                $messages[] = $status['message'];
            }

            if (isset($status['form_errors'])) {
                $messages[] = '' . json_encode($status['form_errors'], JSON_UNESCAPED_UNICODE);
            }

            if (empty($messages)) {
                $body_sample = sprintf('Body: [%s]', substr($body, 0, $this->config->getErrorBodyLength()));

                $messages[] = 'Unknown error. ' . $body_sample;
            }

            throw new AppException(implode('|', $messages), $statusCode);
        }

        return $json['data'];
    }

    /**
     * @param $filename
     *
     * @return LazyOpenStream
     * @throws RequestException
     */
    protected function getFileStream($filename)
    {
        if (!$filename) {
            throw new RequestException(null, sprintf('File not exists [%s]', $filename));
        }

        return new LazyOpenStream($filename, 'rb');
    }

    protected static function camelCaseToUnderscore($name)
    {
        $name = ltrim(preg_replace('/[A-Z]/', '_$0', $name), '_');

        return strtolower($name);
    }
}
