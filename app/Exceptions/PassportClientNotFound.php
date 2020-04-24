<?php

namespace App\Exceptions;

use Throwable;

class PassportClientNotFound extends \RuntimeException
{
    public function __construct(
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
