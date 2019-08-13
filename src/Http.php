<?php

namespace SapeRt\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class Http extends Client
{
    const BASE_URI = 'base_uri';
    const COOKIES  = 'cookies';

    /**
     * @return string|null
     */
    public function getBaseUri()
    {
        return $this->getConfig(self::BASE_URI);
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->getConfig(self::COOKIES);
    }

    public function setCookie($name, $value, $domain = null, $path = null)
    {
        if (!$domain) {
            $base_uri = $this->getBaseUri();
            $domain   = $base_uri->getHost();
        }

        $data = [
            'Name'   => $name,
            'Value'  => $value,
            'Domain' => $domain,
        ];

        if ($path) {
            $data['Path'] = $path;
        }

        $cookie = new SetCookie($data);

        $this->getCookieJar()->setCookie($cookie);

        return $this;
    }
}
