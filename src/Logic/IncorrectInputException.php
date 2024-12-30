<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Throwable;

/**
 * IncorrectInputException
 *
 * A custom exception class designed for handling incorrect form input.
 * This class extends the base `Exception` class in PHP and allows for
 * additional customization in handling input validation errors.
 *
 * @package Logic
 * @author Erik Vaněk
 */
class IncorrectInputException extends Exception
{
    /**
     * Constructor for the custom exception.
     *
     * Initializes the exception with a custom message, an optional error code,
     * and an optional previous exception for chaining purposes.
     *
     * @param string $message The error message for the exception.
     * @param int $code (optional) The error code for the exception (default: 0).
     * @param Throwable|null $previous (optional) The previous exception used for exception chaining.
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
