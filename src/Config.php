<?php

namespace SapeRt\Api;


use SapeRt\Api\Client\Base;

class Config
{
    const HOST = 'https://traffic.sape.ru';

    const XDEBUG_CONFIG  = 'XDEBUG_CONFIG';
    const XDEBUG_SESSION = 'XDEBUG_SESSION';
    const IDEKEY         = 'idekey';

    /** @var int */
    protected $errorBodyLength = 4096;

    /** @var string */
    protected $url = '';

    /** @var string */
    protected $authType;

    /** @var callable[] */
    protected $configurators = [];

    /** @var bool */
    protected $xdebugIdekey = false;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function __construct(string $auth, string $host = '')
    {
        $this->authType = $auth;
        if ($host === '') {
            $host = self::HOST;
        }
        $this->url = $host;
    }

    public function getHost(): string
    {
        return parse_url($this->getUrl(), PHP_URL_HOST);
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

    public function setXdebugSession($idekey = null)
    {
        if (!$idekey) {
            $idekey = $this->getXdebugIdekey();
        }
        $this->xdebugIdekey = $idekey;

        $this->addConfigurator(function (Base $client) {
            $client->getHttpClient()->setCookie(self::XDEBUG_SESSION,
                $this->xdebugIdekey, $this->getHost());
        });

        return $this;
    }

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function getErrorBodyLength(): int
    {
        return $this->errorBodyLength;
    }

    public function getConfigurators(): array
    {
        return $this->configurators;
    }

    public function addConfigurator($configurator)
    {
        $this->configurators[] = $configurator;

        return $this;
    }
}
