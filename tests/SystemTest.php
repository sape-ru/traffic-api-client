<?php

namespace SapeRt\Api\Tests;

use SapeRt\Api\Exception\Exception;

include_once __DIR__ . '/bootstrap.php';

class SystemTest extends TestBase
{
    /**
     * @throws Exception
     */
    public function testDictionary()
    {
        $data = $this->client->dictionary_dict(true);

        static::assertGreaterThan(0, $data);

        static::assertTrue($this->handler->hasInfoThatContains('request'));
        static::assertTrue($this->handler->hasInfoThatContains('response'));
    }

    /**
     * @expectedException \SapeRt\Api\Exception\HttpException
     * @throws Exception
     */
    public function testLogin()
    {
        $login = 'badLogin';
        $token = 'badToken';

        $this->client->system_login($login, $token);
    }
}
