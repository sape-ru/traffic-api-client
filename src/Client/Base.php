<?php

namespace SapeRt\Api\Client;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SapeRt\Api\Config;
use SapeRt\Api\Exception\AppException;
use SapeRt\Api\Exception\ConfigException;
use SapeRt\Api\Exception\Exception;
use SapeRt\Api\Exception\RequestException;
use SapeRt\Api\Exception\ResponseException;
use SapeRt\Api\Http;

abstract class Base implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';

    const ONLINE      = 'online';
    const JSON_TYPE   = 'json_type';
    const FORM_ERRORS = 'form_errors';
    const MESSAGE     = 'message';
    const IS_SUCCESS  = 'is_success';
    const QUERY       = 'query';
    const MULTIPART   = 'multipart';
    const NAME        = 'name';
    const CONTENTS    = 'contents';
    const FILENAME    = 'filename';
    const NEXT        = '/';

    /** @var array Default params for the query */
    protected $params = array();

    /** @var Http */
    protected $http;

    /** @var Config */
    protected $config;

    public function __construct(Config $config, $http = null)
    {
        $this->config = $config;

        foreach ($config->getConfigurators() as $configurator) {
            $configurator($this);
        }

        $this->http = $http;
    }

    /* Setter & getters */

    public static function toEndPoint($name)
    {
        $method = str_replace('_', self::NEXT, $name);
        $method = str_replace('_', '-',
            self::camelCaseToUnderscore($method));

        return $method;
    }

    public function setOnline($value)
    {
        return $this->setParam(self::ONLINE, (int)$value);
    }

    public function getOnline()
    {
        return $this->getParam(self::ONLINE);
    }

    public function setJsonType($value)
    {
        return $this->setParam(self::JSON_TYPE, $value);
    }

    public function getJsonType($default = null)
    {
        return $this->getParam(self::JSON_TYPE, $default);
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
        return array_filter((array) $this->params,
            static function ($i) { return $i !== null; });
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
    public function getBaseUrl(): string
    {
        $auth = $this->getConfig()->getAuth();
        if (!$auth) {
            throw new ConfigException(
                'API authentication type is undefined');
        }

        $baseUrl = $this->getConfig()->getBaseUrl() . $auth;

        return $baseUrl;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return Http
     */
    public function getHttpClient(): Http
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
    public function system_login($login, $token): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['login' => $login,
                                             'token' => $token]);
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
    public function dictionary_dict($names_only = false, $include = [],
                                    $exclude = []): array
    {
        $params['names-only'] = $names_only;
        $include and $params['include'] = $include;
        $exclude and $params['exclude'] = $exclude;

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * Базовый метод запроса
     *
     * @param string $method   HTTP-метод
     * @param string $path     URI-Путь
     * @param array  $params   Query-Параметры
     * @param array  $data     Данные запроса
     * @param array  $files    Загрузить файлы (будет использован POST)
     * @param bool   $postdata Передавать данные через POST (по-умолчанию данные передаются в виде JSON)
     *
     * @return array
     * @throws Exception
     */
    protected function request($method, $path, array $params = [],
                               array $data = [], array $files = [],
                               $postdata = false): array
    {
        $params = array_merge($this->getParams(), $params);

        $options = [];

        $uri   = $this->getBaseUrl() . self::NEXT . $path;
        $label = '';
        if ($this->logger) {
            $label = md5(json_encode([$method, $uri, $options]));
        }

        if ($params) {
            $options[self::QUERY] = $params;
        }

        try {
            if ($files) {
                $options[self::MULTIPART] = [];
                foreach ($files as $key => $filename) {
                    if (!is_array($filename)) {
                        $options[self::MULTIPART][] = [
                            self::NAME     => $key,
                            self::CONTENTS => $this->getFileStream($filename),
                            self::FILENAME => $filename,
                        ];
                    } else {
                        foreach ($filename as $f) {
                            $options[self::MULTIPART][] = [
                                self::NAME     => $key,
                                self::CONTENTS => $this->getFileStream($f),
                                self::FILENAME => $filename,
                            ];
                        }
                    }
                }

                if ($data) {
                    foreach ($data as $key => $value) {
                        $options[self::MULTIPART][] = [
                            self::NAME     => $key,
                            self::CONTENTS => $value,
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

            $isExists = array_key_exists(self::QUERY, $options);
            $query    = '';
            if ($isExists) {
                $query = $options[self::QUERY];
            }
            if ($this->logger) {

                $url = $uri . ($query ? '?'
                        . http_build_query($query) : '');
                $this->logger->info(sprintf('request[%s]: %s %s',
                    $label, $method, $url));
            }

            /** @var ResponseInterface $response */
            $response = $this->getHttpClient()->request($method, $uri,
                $options);
        } catch (GuzzleException $e) {
            $request = null;
            if ($e instanceof GuzzleRequestException) {
                $request = $e->getRequest();
            }

            if ($this->logger) {
                $this->logger->error(sprintf('error[%s]: %s %s',
                    $label, $e->getCode(), $e->getMessage()));
            }

            throw new RequestException($request,
                'Cannot perform request', $e->getCode(), $e);
        }

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();

        if ($this->logger) {
            $this->logger->info(sprintf('response[%s]: %s %s',
                $label, $statusCode, $body));
        }

        $json   = @json_decode($body, true);
        $status = @$json['status'];

        if (!isset($status[self::IS_SUCCESS])) {
            $body_sample = sprintf('Body: [%s]',
                substr($body, 0, $this->getConfig()
                                      ->getErrorBodyLength()));

            if ($statusCode < 400) {
                $message = 'Decode error. ' . $body_sample;
                $code    = $statusCode;
            } else {
                $message = 'Response error. ' . $body_sample;
                $code    = 555;
            }

            throw new ResponseException($message, $code);
        }

        if (!$status[self::IS_SUCCESS]) {
            if (isset($status[self::MESSAGE])) {
                $messages[] = $status[self::MESSAGE];
            }

            if (isset($status[self::FORM_ERRORS])) {
                $messages[] = '' . json_encode($status[self::FORM_ERRORS],
                        JSON_UNESCAPED_UNICODE);
            }

            if (empty($messages)) {
                $body_sample = sprintf('Body: [%s]',
                    substr($body, 0, $this->getConfig()
                                          ->getErrorBodyLength()));

                $messages[] = 'Unknown error. ' . $body_sample;
            }

            throw new AppException(implode('|', $messages),
                $statusCode);
        }

        return $json['data'];
    }

    /**
     * @param $filename
     *
     * @return LazyOpenStream
     * @throws RequestException
     */
    protected function getFileStream($filename): LazyOpenStream
    {
        if (!$filename) {
            throw new RequestException(null,
                sprintf('File not exists [%s]', $filename));
        }

        return new LazyOpenStream($filename, 'rb');
    }

    protected static function camelCaseToUnderscore($name)
    {
        $name = ltrim(preg_replace('/[A-Z]/', '_$0', $name), '_');

        return strtolower($name);
    }
}
