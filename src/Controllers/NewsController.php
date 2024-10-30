<?php

declare(strict_types=1);

namespace Controllers;

class NewsController extends Controller
{
    private string $page = '../src/Views/news.php';

    public function render(): void
    {
        require_once $this->page; // Load page content
    }
}
