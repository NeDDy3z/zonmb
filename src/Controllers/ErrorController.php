<?php

declare(strict_types=1);

namespace Controllers;


/**
 * ErrorController
 *
 * This controller is responsible for handling error-related functionality. It facilitates:
 * - Rendering error pages for various HTTP status codes (e.g., 404, 500).
 * - Logging error details for debugging and analysis purposes.
 * - Providing user-friendly error messages to improve the user experience.
 * - Offering utility methods to deal with custom application errors or exceptions.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class ErrorController extends Controller
{
    /**
     * @var string $path Path to error.php view file
     */
    private string $path = ROOT . 'src/Views/error.php';

    /**
     * @var int $errorCode The HTTP status code for the error.
     */
    private int $errorCode;

    /**
     * ErrorController constructor.
     *
     * Initializes an instance of the ErrorController with the provided error code.
     * If no error code is specified, it defaults to 404 (Not Found).
     *
     * @param int $errorCode The HTTP error code to render (default: 404).
     */
    public function __construct(int $errorCode = 404)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * Render the error page.
     *
     * This method sets the appropriate HTTP response code and loads the `error.php`
     * view file to display the error page to the user. The page informs the user of
     * the error that occurred.
     *
     * @return void
     */
    public function render(): void
    {
        http_response_code($this->errorCode);
        require_once $this->path; // Load page content
    }
}
