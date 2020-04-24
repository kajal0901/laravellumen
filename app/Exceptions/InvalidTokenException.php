<?php

namespace Illuminate\Routing\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidTokenException extends HttpException
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        $message = ($message == '') ? __('invalid_token') : $message;
        parent::__construct($message, 403);
        $this->message = $message;
    }
}
