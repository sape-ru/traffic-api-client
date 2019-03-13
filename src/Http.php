<?php

namespace SapeRt\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class Http extends Client
{
    /**
     * @return string|null
     */
    public function getBaseUri()
    {
        return $this->getConfig('base_uri');
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->getConfig('cookies');
    }

    public function setCookie($name, $value, $domain = null, $path = null)
    {
        if (!$domain) {
            $base_uri = $this->getConfig('base_uri');
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
