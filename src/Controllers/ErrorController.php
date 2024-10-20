<?php

namespace Controllers;

class ErrorController implements IController {

    private string $path = 'src/Views/error.php';

    public function render(): void {
        require_once $this->path; // Load page content
    }
}