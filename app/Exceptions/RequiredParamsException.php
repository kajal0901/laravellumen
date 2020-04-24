<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class RequiredParamsException extends \Exception
{
    protected $message;
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * CustomModelNotFound constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message, $this->code);
        $this->message = $message;
    }
}
