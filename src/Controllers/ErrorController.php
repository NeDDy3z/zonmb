<?php
declare(strict_types=1);

namespace Zonmb\Controllers;

class ErrorController {

    private string $path = 'src/Views/error.php';

    public function render(): void {
        require_once $this->path; // Load page content
    }
}