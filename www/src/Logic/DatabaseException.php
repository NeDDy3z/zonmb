<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Throwable;
use Models\DatabaseConnector;

class DatabaseException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        DatabaseConnector::close();
        parent::__construct($message, $code, $previous);
    }
}
