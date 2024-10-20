<?php

namespace Controllers;

class HomepageController implements IController {

    private string $path = 'src/Views/homepage.php';

    public function render(): void {
        require_once $this->path; // Load page content
    }
}