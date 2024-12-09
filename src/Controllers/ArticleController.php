<?php

namespace Controllers;

use Exception;
use Helpers\PrivilegeRedirect;
use Logic\Article;
use Logic\DatabaseException;
use Models\DatabaseConnector;

class ArticleController
{
    /**
     * @var string $page
     */
    private string $page = ROOT . 'src/Views/article.php';
    private string $editorPage = ROOT . 'src/Views/article-editor.php';

    /**
     * @var string $subPage
     */
    private string $subPage;

    /**
     * Constructor
     * @param string|null $subPage
     */
    public function __construct(?string $subPage)
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $this->subPage = $subPage ?? '';

        switch ($this->subPage) {
            case '': // redirect on request for all articles
                $this->getArticles();
                break;
            case 'add':
            case 'edit':
                $this->page = $this->editorPage;
                $privilegeRedirect->redirectUser();
                break;
            default: // on any other article continue
                break;
        }
    }

    /**
     * Render webpage
     * @throws Exception
     */
    public function render(): void // TODO: Fix the slash /01 in the URL
    {
        switch ($this->subPage) {
            case 'add':
            case 'edit':
                $article = Article::getArticleById($_GET['id'] ?? null);
                $this->page = $this->editorPage;
                break;
            default: Article::getArticleBySlug($this->subPage);
        };

        $user = $_SESSION['user_data'] ?? null;
        $type = $this->subPage;
        require_once $this->page; // Load page content
    }


    /**
     * Get articles from DB
     */
    public function getArticles(): void
    {
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $page = $_GET['page'] ?? 1;

        $conditions = ($search) ? "WHERE id LIKE $search OR title LIKE '$search' OR subtitle LIKE '$search' OR 
                                    content LIKE '$search' or author LIKE '$search' OR created_at LIKE '$search'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

        try {
            $articlesData = DatabaseConnector::selectArticles(
                conditions: $conditions,
            );

            if (!$articlesData) {
                throw new Exception('No articles found');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }

        echo json_encode($articlesData);
        exit();
    }

    public function addArticle()
    {
    }
}
