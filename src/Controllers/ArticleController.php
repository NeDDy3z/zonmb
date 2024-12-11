<?php

namespace Controllers;

use Exception;
use Helpers\PrivilegeRedirect;
use Logic\Article;
use Logic\DatabaseException;
use Models\ArticleModel;
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
                $privilegeRedirect->redirectUser();
                $this->page = $this->editorPage;
                break;
            case 'delete':
                $privilegeRedirect->redirectUser();
                $this->deleteArticle();
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
            case 'delete':
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
            $articlesData = ArticleModel::selectArticles(
                conditions: $conditions,
            );

            if (!$articlesData) {
                throw new Exception('Žádné články nebyly nalezeny');
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

    public function deleteArticle(): void
    {
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'Chybí ID článku']);
        } else {
            try {
                switch (true) {
                    case isset($_GET['img']):
                        $imagePaths = explode(',', ArticleModel::selectArticle(conditions: 'WHERE id = '. $_GET['id'])['image_paths']);
                        $newImagePaths = array_diff($imagePaths, [$_GET['img']]);

                        $newImagePaths = (count($newImagePaths) === 0) ? ['null'] : $newImagePaths;

                        ArticleModel::updateArticle(id: $_GET['id'], imagePaths: $newImagePaths);
                        echo json_encode(['success' => 'imageDelete']);
                        break;
                    default:
                        ArticleModel::removeArticle(id: $_GET['id']);
                        echo json_encode(['success' => 'articleDelete']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }

        exit();
    }
}
