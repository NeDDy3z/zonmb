<?php

namespace Controllers;

class NewsController implements IController {

    private string $page = 'src/Views/news.php';

    public function render(): void {
        require_once $this->page; // Load page content
    }
}
