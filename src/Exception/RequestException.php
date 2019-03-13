<?php

namespace SapeRt\Api\Exception;

use Psr\Http\Message\RequestInterface;

class RequestException extends HttpException
{
    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct($request, $message = '', $code = 0, $previous = null)
    {
        $this->request = $request;

        parent::__construct($message, $code, $previous);
    }

    public function getRequest()
    {
        return $this->request;
    }

}
