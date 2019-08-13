<?php

namespace SapeRt\Api;


use ArrayAccess;
use Exception;
use RuntimeException;
use SapeRt\Api\Client\Base;

class Config implements ArrayAccess
{
    const HOST = 'https://traffic.sape.ru';

    const XDEBUG_CONFIG  = 'XDEBUG_CONFIG';
    const XDEBUG_SESSION = 'XDEBUG_SESSION';
    const IDEKEY         = 'idekey';
    const ACCESSOR_GET   = 'get';
    /** @var int */
    protected $_error_body_length = 4096;

    /** @var string */
    protected $_host = '';

    /** @var string */
    protected $_auth;

    /** @var callable[] */
    protected $_configurators = [];

    /** @var bool */
    protected $_is_dev = false;

    /** @var bool */
    protected $_xdebug_ide_key = false;

    public function getBaseUrl()
    {
        return $this->_is_dev
            ? $this->getDevHost()
            : $this->_host;
    }

    protected static function snakeCaseToCamelCase($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return $name;
    }

    public function __construct(
        $auth, $host = '')
    {
        $this->_auth = $auth;
        if ($host === '') {
            $host = self::HOST;
        }
        $this->_host = $host;
    }

    public function getHost()
    {
        return parse_url($this->getBaseUrl(), PHP_URL_HOST);
    }

    public function getXdebugIdekey()
    {
        $config = getenv(self::XDEBUG_CONFIG);

        if (!$config) {
            return null;
        }

        $config = array_map('trim', explode(' ', $config));
        foreach ($config as $cfg) {
            list($name, $value) = array_map('trim', explode('=', $cfg));

            if ($name === self::IDEKEY) {
                return $value;
            }
        }

        return null;
    }

    public function setXDebugSession($idekey = null)
    {
        if (!$idekey) {
            $idekey = $this->getXdebugIdekey();
        }
        $this->_xdebug_ide_key = $idekey;

        $this->addConfigurator(function (Base $client) {
            $client->getHttpClient()->setCookie(self::XDEBUG_SESSION,
                $this->_xdebug_ide_key, $this->getHost());
        });

        return $this;
    }

    public static function getDevName()
    {
        $path_parts = explode(DIRECTORY_SEPARATOR, __FILE__);
        if ($path_parts[3] === 'sape_ru') {
            return '';
        }

        return $path_parts[3];
    }

    protected function getDevHost($devPrefix = '-dev-')
    {
        $devName = self::getDevName();
        if (empty($devName)) {
            throw new RuntimeException('Cannot detect dev user name');
        }

        $devNamePrefix = $devName . $devPrefix;

        $prodHost = parse_url($this->_host, PHP_URL_HOST);
        $devHost  = $devNamePrefix . $prodHost;

        $r = str_replace($prodHost, $devHost, $this->_host);

        return $r;
    }

    public function setDevelopment(bool $dev): self
    {
        $this->_is_dev = $dev;

        return $this;
    }

    public function getAuth(): string
    {
        return $this->_auth;
    }

    public function getErrorBodyLength(): int
    {
        return $this->_error_body_length;
    }

    public function getConfigurators(): array
    {
        return $this->_configurators;
    }

    public function addConfigurator($configurator)
    {
        $this->_configurators[] = $configurator;

        return $this;
    }

    /**
     * @param $fieldName
     *
     * @return mixed
     * @throws Exception
     */
    public function get($fieldName)
    {
        $accessor = self::ACCESSOR_GET
            . self::snakeCaseToCamelCase($fieldName);
        if (is_callable([$this, $accessor])) {
            return $this->$accessor();
        }

        throw new RuntimeException(sprintf('No getter [%s]', $fieldName));
    }

    // ArrayAccess

    public function offsetExists($offset):bool
    {
        $accessor = self::ACCESSOR_GET
            . self::snakeCaseToCamelCase($offset);

        return is_callable([$this, $accessor]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     * @throws Exception
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
