<?php

namespace SapeRt\Api;

use SapeRt\Api\Exception\ConfigException;

class Config implements \ArrayAccess
{
    /** @var int */
    protected $_error_body_length = 512;

    /** @var string */
    protected $_base_url = 'https://traffic.sape.ru/api';

    /** @var string */
    protected $_namespace;

    /** @var callable[] */
    protected $_configurators = [];


    protected static function underscoreToCamelCase($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return $name;
    }


    public function __construct($namespace)
    {
        $this->_namespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->_namespace;
    }

    public function getBaseUrl()
    {
        return $this->_base_url;
    }

    public function getErrorBodyLength()
    {
        return $this->_error_body_length;
    }

    public function getConfigurators()
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
     * @throws ConfigException
     */
    public function get($fieldName)
    {
        $accessor = 'get' . self::underscoreToCamelCase($fieldName);
        if (is_callable([$this, $accessor])) {
            return $this->$accessor();
        }

        throw new ConfigException(sprintf('No getter [%s]', $fieldName));
    }

    // ArrayAccess

    public function offsetExists($offset)
    {
        $accessor = 'get' . self::underscoreToCamelCase($offset);

        return is_callable([$this, $accessor]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     * @throws ConfigException
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
