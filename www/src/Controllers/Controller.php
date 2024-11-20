<?php

declare(strict_types=1);

namespace Controllers;

class Controller
{
    private string $page = 'views/homepage.php';

    public function render(): void
    {
        require_once $this->page;
    }
}
