<?php

namespace Controllers;

class ErrorController {
    public function render(): void {
        $title = "ZONMB";

        require 'src/Views/error.php'; // Import page content
    }
}