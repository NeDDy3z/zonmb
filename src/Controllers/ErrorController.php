<?php

declare(strict_types=1);

namespace Controllers;

class ErrorController extends Controller
{
    /**
     * @var string $path
     */
    private string $path = ROOT . 'src/Views/error.php';
    /**
     * @var int $errorCode
     */
    private int $errorCode;

    /**
     * Construct
     * @param int $errorCode
     */
    public function __construct(int $errorCode = 404)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        http_response_code($this->errorCode);
        require_once $this->path; // Load page content
    }
}
