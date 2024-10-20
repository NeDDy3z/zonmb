<?php
declare(strict_types=1);

namespace Zonmb\Controllers;

class HomepageController implements IController {

    private string $path = 'src/Views/homepage.php';

    public function render(): void {
        require_once $this->path; // Load page content
    }
}