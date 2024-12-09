<?php

declare(strict_types=1);

namespace Controllers;

use Logic\Article;
use Logic\DatabaseException;
use Models\DatabaseConnector;

class NewsController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = ROOT . 'src/Views/news.php';

    /**
     * Render webpage
     * @return void
     * @throws DatabaseException
     */
    public function render(): void
    {
        // TODO: Implement pagination - Move to XMLHTTPRequest
        $articles = $this->loadArticles();

        require_once $this->page; // Load page content
    }

    /**
     * Load articles from database
     * @return array<Article>
     * @throws DatabaseException
     */
    private function loadArticles(): array
    {
        $articles = DatabaseConnector::selectArticles();
        $articlesArray = [];

        foreach ($articles as $article) {
            $articlesArray[] = new Article(
                id: (int)$article['id'],
                title: $article['title'],
                subtitle: $article['subtitle'],
                content: $article['content'],
                slug: $article['slug'],
                imagePaths: explode(',', $article['image_paths']),
                authorId: (int)$article['author_id'],
                createdAt: $article['created_at'],
            );
        }

        return $articlesArray;
    }
}
