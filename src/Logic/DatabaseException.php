<?php

namespace Logic;

use Exception;
use Models\DatabaseConnector;
use Throwable;

class DatabaseException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        DatabaseConnector::isOpenThenClose();
        parent::__construct($message, $code, $previous);
    }
}