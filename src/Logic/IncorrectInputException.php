<?php

namespace Logic;

use Exception;
use Throwable;

class IncorrectInputException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}