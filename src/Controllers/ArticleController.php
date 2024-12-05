<?php

namespace Controllers;

use Exception;
use Logic\Article;
use Logic\DatabaseException;

class ArticleController
{
    private string $page = ROOT . 'src/Views/article.php';

    /**
     * @throws Exception
     */
    public function render(): void
    {
        $slug = $_GET['slug'] ?? null;

        if (!$slug) {
            (new ErrorController(404))->render();
            return;
        }

        try {
            $article = Article::getArticleBySlug($slug);
        } catch (Exception $e) {
            (new ErrorController(404))->render();
            return;
        }

        require_once $this->page; // Load page content
    }

    public function addArticle()
    {

    }
}
