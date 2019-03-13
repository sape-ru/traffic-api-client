<?php

namespace SapeRt\Api\Tests;

include_once __DIR__ . '/bootstrap.php';

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use SapeRt\Api\Client\User;

class TestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $client;

    /**
     * @var TestHandler
     */
    protected $handler;


    public function setUp()
    {
        $this->client = new User;

        $this->handler = new TestHandler();
        $logger        = new Logger('request-traffic');
        $logger->pushHandler($this->handler);

        $this->client->setLogger($logger);
    }
}
