<?php

namespace SapeRt\Api\Client;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SapeRt\Api\Config;
use SapeRt\Api\Exception\AppException;
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
    const STATUS      = 'status';
    const DATA        = 'data';

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
            self::camelCaseToSnakeCase($method));

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
     */
    public function getBaseUrl(): string
    {
        $config  = $this->getConfig();
        $baseUrl = $config->getUrl() . $config->getAuthType();

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
     * @param string $method         HTTP-метод
     * @param string $path           URI
     * @param array  $params         Параметры в строке адреса (Query)
     * @param array  $data           Данные запроса (Body)
     * @param array  $files          Загрузить файлы
     *                               (будет использован POST)
     * @param bool   $sendDataAsJson Передавать данные как JSON
     *                               (иначе данные передаются как параметры формы)
     *
     * @return array
     * @throws Exception
     */
    protected function request($method, $path, array $params = [],
                               array $data = [], array $files = [],
                               $sendDataAsJson = true): array
    {
        $options = [];
        $params  = array_merge($this->getParams(), $params);
        if ($params) {
            $options[self::QUERY] = $params;
        }

        $uri       = $this->getBaseUrl() . self::NEXT . $path;
        $label     = '';
        $hasLogger = !empty($this->logger);
        if ($hasLogger) {
            $label = md5(json_encode([$method, $uri, $options]));
        }

        $response = null;
        try {
            $response = $this->performRequest($method, $data, $files,
                $sendDataAsJson, $options, $uri, $label);
        } catch (GuzzleException $e) {
            $this->processRequestFail($e, $label);
        }

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();
        if ($hasLogger) {
            $this->logger->info(sprintf('response[%s]: %s %s',
                $label, $statusCode, $body));
        }

        $asJson = @json_decode($body, true);
        $status = @$asJson[self::STATUS];
        $this->processResponseFail($status, $body, $statusCode);
        $this->processApplicationFail($status, $body, $statusCode);

        return $asJson[self::DATA];
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

    protected static function camelCaseToSnakeCase($name)
    {
        $name = ltrim(preg_replace('/[A-Z]/', '_$0', $name), '_');

        return strtolower($name);
    }

    /**
     * @param array $data
     * @param array $files
     * @param array $options
     *
     * @return Base
     * @throws RequestException
     */
    protected function processFiles(array $data, array $files,
                                    array &$options): self
    {
        $options[self::MULTIPART] = [];
        foreach ($files as $key => $filename) {
            $hasManyFiles = is_array($filename);
            if ($hasManyFiles) {
                foreach ($filename as $f) {
                    $options[self::MULTIPART][] = [
                        self::NAME     => $key,
                        self::CONTENTS => $this->getFileStream($f),
                        self::FILENAME => $filename,
                    ];
                }
            }
            if (!$hasManyFiles) {
                $options[self::MULTIPART][] = [
                    self::NAME     => $key,
                    self::CONTENTS => $this->getFileStream($filename),
                    self::FILENAME => $filename,
                ];
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

        return $this;
    }

    /**
     * @param                                   $status
     * @param StreamInterface                   $body
     * @param int                               $statusCode
     *
     * @return Base
     * @throws ResponseException
     */
    protected function processResponseFail(
        $status, StreamInterface $body,
        int $statusCode): self
    {
        $bodySample = '';
        $isSuccess  = isset($status[self::IS_SUCCESS]);
        if (!$isSuccess) {
            $bodySample = sprintf('Body: [%s]',
                substr($body, 0, $this->getConfig()
                                      ->getErrorBodyLength()));
        }
        $message       = '';
        $code          = 0;
        $isDecodeError = $statusCode < 400;
        if (!$isSuccess && $isDecodeError) {
            $message = 'Decode error. ' . $bodySample;
            $code    = $statusCode;
        }
        if (!$isSuccess && !$isDecodeError) {
            $message = 'Response error. ' . $bodySample;
            $code    = 555;
        }
        if (!$isSuccess) {
            throw new ResponseException($message, $code);
        }

        return $this;
    }

    /**
     * @param                                   $status
     * @param StreamInterface                   $body
     * @param int                               $statusCode
     *
     * @return Base
     * @throws AppException
     */
    protected function processApplicationFail(
        $status, StreamInterface $body,
        int $statusCode): self
    {
        $isFail     = !$status[self::IS_SUCCESS];
        $hasMessage = isset($status[self::MESSAGE]);
        $messages   = [];
        if ($isFail && $hasMessage) {
            $messages[] = $status[self::MESSAGE];
        }

        $hasFormError = isset($status[self::FORM_ERRORS]);
        if ($isFail && $hasFormError) {
            $messages[] = '' . json_encode($status[self::FORM_ERRORS],
                    JSON_UNESCAPED_UNICODE);
        }

        if ($isFail && empty($messages)) {
            $messages[] = 'Unknown error. '
                . sprintf('Body: [%s]', $body);
        }
        if ($isFail) {
            throw new AppException(implode('|', $messages),
                $statusCode);
        }

        return $this;
    }

    /**
     * @param GuzzleException $e
     * @param string          $label
     *
     * @throws RequestException
     */
    protected function processRequestFail(GuzzleException $e,
                                          string $label)
    {
        $request = null;
        if ($e instanceof GuzzleRequestException) {
            $request = $e->getRequest();
        }
        if (!empty($this->logger)) {
            $this->logger->error(sprintf('error[%s]: %s %s',
                $label, $e->getCode(), $e->getMessage()));
        }

        throw new RequestException($request,
            'Cannot perform request', $e->getCode(), $e);
    }

    /**
     * @param        $method
     * @param array  $data
     * @param array  $files
     * @param        $sendDataAsJson
     * @param array  $options
     * @param string $uri
     * @param string $label
     *
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws RequestException
     */
    protected function performRequest(
        $method, array $data, array $files, $sendDataAsJson,
        array $options, string $uri, string $label): ResponseInterface
    {
        if ($files) {
            $this->processFiles($data, $files, $options);
        }
        if (!$files && $data) {
            $options = self::processData($data, $sendDataAsJson, $options);
        }

        if ($this->logger) {
            self::logRequest($method, $options, $uri, $label, $this->logger);
        }

        /** @var ResponseInterface $response */
        $response = $this->getHttpClient()->request($method, $uri,
            $options);

        return $response;
    }

    /**
     * @param array $data
     * @param       $sendDataAsJson
     * @param array $options
     *
     * @return array
     */
    protected static function processData(array $data, $sendDataAsJson,
                                          array &$options): array
    {
        if ($sendDataAsJson) {
            $options['json'] = $data;
        }
        if (!$sendDataAsJson) {
            $options['form_params'] = $data;
        }

        return $options;
    }

    /**
     * @param                 $method
     * @param array           $options
     * @param string          $uri
     * @param string          $label
     * @param LoggerInterface $logger
     */
    protected static function logRequest($method, array $options, string $uri,
                                         string $label, LoggerInterface $logger)
    {
        $hasQuery = array_key_exists(self::QUERY, $options);
        $url      = $uri;
        if ($hasQuery) {
            $query = $options[self::QUERY];
            $url   = $uri . '?' . http_build_query($query);
        }

        $logger->info(sprintf('request[%s]: %s %s',
            $label, $method, $url));
    }
}
