<?php
declare(strict_types=1);

namespace Zonmb\Controllers;

class NewsController implements IController {

    private string $page = 'src/Views/news.php';

    public function render(): void {
        require_once $this->page; // Load page content
    }
}
