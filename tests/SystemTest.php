<?php

namespace SapeRt\Api\Tests;

include_once __DIR__ . '/bootstrap.php';

class SystemTest extends TestBase
{
    public function testDictionary()
    {
        $data = $this->client->dictionary_dict(true);

        static::assertGreaterThan(0, $data);

        static::assertTrue($this->handler->hasInfoThatContains('request'));
        static::assertTrue($this->handler->hasInfoThatContains('response'));
    }

    /**
     * @expectedException \SapeRt\Api\Exception\HttpException
     */
    public function testLogin()
    {
        $login = 'badLogin';
        $token = 'badToken';

        $res = $this->client->system_login($login, $token);
    }
}
