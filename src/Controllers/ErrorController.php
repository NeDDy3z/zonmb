<?php

declare(strict_types=1);

namespace Controllers;

class ErrorController extends Controller
{
    private string $path = '../src/Views/error.php';
    private int $errorCode;

    /**
     * @param int $errorCode
     */
    public function __construct(int $errorCode = 404)
    {
        $this->errorCode = $errorCode;
    }

    public function render(): void
    {
        http_response_code($this->errorCode);
        require_once $this->path; // Load page content
    }
}
