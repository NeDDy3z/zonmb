<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Throwable;
use Models\DatabaseConnector;

/**
 * DatabaseException
 *
 * A custom exception class specifically designed for handling database-related errors.
 * Extends the base `Exception` class in PHP and ensures the database connection is closed
 * whenever such an exception is thrown.
 *
 * @package Logic
 * @author Erik Vaněk
 */
class DatabaseException extends Exception
{
    /**
     * Constructor for the custom exception.
     *
     * Upon instantiation, it closes the active database connection (if any) using the `DatabaseConnector::close()` method,
     * and initializes the exception with a custom message, an optional error code, and an optional previous exception
     * for chaining purposes.
     *
     * @param string $message The error message for the exception.
     * @param int $code (optional) The error code for the exception (default: 0).
     * @param Throwable|null $previous (optional) The previous exception used for exception chaining.
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        DatabaseConnector::close();
        parent::__construct($message, $code, $previous);
    }
}
