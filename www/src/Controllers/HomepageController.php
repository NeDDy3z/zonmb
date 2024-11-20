<?php

declare(strict_types=1);

namespace Controllers;

class HomepageController extends Controller
{
    private string $path = 'src/Views/homepage.php';

    public function render(): void
    {
        require_once $this->path; // Load page content
    }
}
