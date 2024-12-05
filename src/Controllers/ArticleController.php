<?php

namespace Controllers;

use Exception;
use Logic\Article;
use Logic\DatabaseException;

class ArticleController
{
    private string $page = ROOT . 'src/Views/article.php';
    private string $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @throws Exception
     */
    public function render(): void
    {
        if ($this->slug === '') {
            (new ErrorController(404))->render();
            return;
        }

        try {
            $article = Article::getArticleBySlug($this->slug);
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
