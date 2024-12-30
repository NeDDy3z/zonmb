<?php

namespace Controllers;

use Exception;
use Helpers\DateHelper;
use Helpers\ImageHelper;
use Helpers\PrivilegeRedirect;
use Helpers\ReplaceHelper;
use Logic\Article;
use Logic\Router;
use Logic\Validator;
use Models\ArticleModel;
use Models\DatabaseConnector;

/**
 * ArticleController
 *
 * The ArticleController class is responsible for handling actions related to articles,
 * such as retrieving, adding, editing, deleting articles and their associated images.
 *
 * It also manages the rendering of views for article-related operations and implements
 * validation and privilege checks where necessary.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class ArticleController extends Controller
{
    /**
     * @var string $page The default article view page path
     */
    private string $page = ROOT . 'src/Views/article.php';

    /**
     * @var string $editorPage The path to the article editor page
     */
    private string $editorPage = ROOT . 'src/Views/article-editor.php';

    /**
     * @var string $action The current action being performed
     */
    private string $action;

    /**
     * @var PrivilegeRedirect $privilegeRedirect The privilege redirect instance for redirecting users
     */
    private PrivilegeRedirect $privilegeRedirect;

    /**
     * @var Validator $validator The validator instance for validating article data
     */
    private Validator $validator;

    /**
     * @var Article|null $article Article to be manipulated with
     */
    private ?Article $article;

    /**
     * Constructor
     *
     * Initializes the controller based on the provided action.
     * Handles routing and checks for user privileges for certain actions.
     *
     * @param string|null $action The action to be performed (e.g., 'get', 'add', 'edit', 'delete', etc.)
     * @throws Exception
     */
    public function __construct(?string $action = '')
    {
        $this->privilegeRedirect = new PrivilegeRedirect();
        $this->validator = new Validator();
        $this->action = $action ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Check for missing ID and redirect
            if ($this->action === 'edit' or $this->action === 'delete') {
                if (!isset($_GET['id'])) {
                    Router::redirect(path: 'error', query: ['error' => 'missingID']);
                }
            }

            switch ($this->action) {
                case 'get':
                    $this->getArticles(
                        $_GET['search'] ?? null,
                        $_GET['sort'] ?? null,
                        $_GET['sortDirection'] ?? null,
                        $_GET['page'] ?? 1,
                    );
                    break;
                case 'exists':
                    $this->existsArticleTitle($_GET['title'] ?? null);
                    break;
                case 'add':
                    $this->privilegeRedirect->redirectUser();
                    $this->page = $this->editorPage;
                    break;
                case 'edit':
                    $this->article = Article::getArticleById($_GET['id']);

                    if (!isset($this->article)) {
                        Router::redirect(path: 'error', query: ['error' => 'incorrectID']);
                    }

                    $this->privilegeRedirect->redirectUser();
                    $this->page = $this->editorPage;
                    break;
                case 'delete':
                    $this->privilegeRedirect->redirectUser();
                    if (isset($_GET['image'])) {
                        $this->deleteArticleImage($_GET['id'], $_GET['image']);
                    } else {
                        $this->deleteArticle($_GET['id']);
                    }
                    break;
                default:
                    $this->article = Article::getArticleBySlug($this->action);

                    if (!isset($this->article)) {
                        Router::redirect(path: 'error', query: ['error' => 'articleNotFound']);
                    }
                    break;
            }
        }
    }

    /**
     * Render the appropriate webpage based on the action.
     *
     * Handles redirection or rendering of article content or the editor page
     * based on the action and provided data.
     *
     * @return void
     * @throws Exception If any error occurs during rendering
     */
    public function render(): void
    {
        switch ($this->action) {
            case 'add':
            case 'edit':
                $this->page = $this->editorPage;
                break;
        }

        $article = $this->article ?? null;
        $user = $_SESSION['user_data'] ?? null;
        $type = $this->action;
        require_once $this->page; // Load page content
    }


    /**
     * Retrieve articles from the database.
     *
     * Supports filtering, sorting, and pagination of retrieved articles.
     * Sends the articles as a JSON response.
     *
     * @param string|null $search
     * @param string|null $sort
     * @param string|null $sortDirection
     * @param int|null $page
     * @return void
     */
    private function getArticles(?string $search = null, ?string $sort = null, ?string $sortDirection = null, ?int $page = 1): void
    {
        // Convert date format
        $search = DateHelper::ifPrettyConvertToISO($search);

        // Create a query
        $conditions = ($search) ? "WHERE id like '$search%' or title LIKE '%$search%' OR subtitle LIKE '%$search%' OR content LIKE '%$search%' OR created_at LIKE '%$search%'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($sortDirection) ? " $sortDirection" : "";
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

    /**
     * Check if an article title exists in the database.
     *
     * @param string $title
     * @return void
     */
    private function existsArticleTitle(string $title): void
    {
        try {
            $exists = ArticleModel::existsArticle($title);
            echo json_encode(['exists' => $exists]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * Add a new article to the database.
     *
     * Validates the provided article data and saves it along with its associated images.
     * Redirects to the created article upon success.
     *
     * @return void
     * @throws Exception If validation or saving data fails
     */
    public function addArticle(): void
    {
        try {
            // Get data from $_POST
            $author = $_POST['author'] ?? null;
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $content = $_POST['content'] ?? null;
            $images = ImageHelper::getUsableImageArray($_FILES['image']) ?? null;

            $this->validator->validateArticle(
                id: null,
                title: $title,
                subtitle: $subtitle,
                content: $content,
            );

            $slug = ReplaceHelper::getUrlFriendlyString($title);
            $articleId = DatabaseConnector::selectMaxId('article') + 1;

            if ($articleId === 1) {
                DatabaseConnector::resetAutoIncrement('article');
            }

            if (isset($images) and $images[0]['tmp_name'] !== '') {
                for ($i = 0; $i < count($images); $i++) {
                    // Generate thumbnail from the first image
                    if ($i === 0) {
                        $thumbnailPath = 'assets/uploads/articles/' . $articleId . '_0_thumbnail.jpeg';
                        $imagePaths[] = $thumbnailPath;
                        ImageHelper::saveImage(
                            image: ImageHelper::resize(ImageHelper::processArticleImage($images[$i]), 360, 200),
                            imagePath: $thumbnailPath,
                        );
                    }

                    // Save image
                    $imagePath = 'assets/uploads/articles/' . $articleId . '_' . $i . '.jpeg';
                    $imagePaths[] = $imagePath; // Add to array
                    ImageHelper::saveImage(
                        image: ImageHelper::resize(ImageHelper::processArticleImage($images[$i]), 800, 450),
                        imagePath: $imagePath,
                    );
                }
            }

            ArticleModel::insertArticle(
                title: $title,
                subtitle: $subtitle,
                content: $content,
                imagePaths: $imagePaths ?? null,
                authorId: $author,
            );


            Router::redirect(path: "articles/$slug", query: ['success' => 'articleAdded']);
        } catch (Exception $e) {
            Router::redirect(path: 'articles/add', query: ['error' => $e->getMessage()]);
        }


    }

    /**
     * Edit an existing article in the database.
     *
     * Allows modification of article data and the addition of new images while
     * preserving or updating existing ones.
     *
     * @return void
     * @throws Exception If validation or saving data fails
     */
    public function editArticle(): void
    {
        try {
            $id = (int)$_POST['id'] ?? null;
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $content = $_POST['content'] ?? null;
            $images = ImageHelper::getUsableImageArray($_FILES['image']);

            // Check titles etc...
            $this->validator->validateArticle(
                id: $id,
                title: $title,
                subtitle: $subtitle,
                content: $content,
            );


            // Check images
            if (isset($images)) {
                foreach ($images as $image) {
                    $this->validator->validateImage($image);
                }
            }

            $imagePaths = ArticleModel::selectArticle(conditions: 'WHERE id = ' . $id)['image_paths'] ?? null;
            if ($imagePaths !== null) {
                $imagePaths = explode(',', $imagePaths);
            }

            try {
                if (isset($images)) {
                    $lastImageId = 0;

                    // Get last img id and add 1 to it
                    foreach ((array)scandir('assets/uploads/articles') as $file) {
                        if (str_starts_with((string)$file, $id . '_') and (int)explode('_', (string)$file)[1] > $lastImageId) {
                            $lastImageId = (int)explode('_', (string)$file)[1];
                        }
                    }

                    $lastImageId++;

                    for ($i = 0; $i < count($images); $i++) {
                        $imagePath = 'assets/uploads/articles/' . $id . '_' . $lastImageId . '.jpeg';
                        $imagePaths[] = $imagePath; // Add to array

                        // Save image
                        ImageHelper::saveImage(
                            image: ImageHelper::processArticleImage($images[$i]),
                            imagePath: $imagePath,
                        );

                        $lastImageId++;
                    }

                    $thumbnailPath = $this->generateThumbnailIfNoneIsPresent($imagePaths);
                    if ($thumbnailPath) {
                        $imagePaths[] = $thumbnailPath;
                    }
                }

                // Update data in DB
                ArticleModel::updateArticle(
                    id: $id,
                    title: $title,
                    subtitle: $subtitle,
                    content: $content,
                    imagePaths: $imagePaths ?? null,
                );

                // Replace slug for a urlfirendlified title
                $slug = ReplaceHelper::getUrlFriendlyString($title);

                Router::redirect(path: "articles/$slug", query: ['success' => 'articleEdited']);
            } catch (Exception $e) {
                Router::redirect(path: "articles/edit", query: ['id' => $id, 'error' => $e->getMessage()]);
            }
        } catch (Exception $e) {
            Router::redirect(path: "articles/edit", query: ['id' => $id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Delete an article and all its associated images.
     *
     * Ensures that all images related to the article are removed from the server
     * when the article is deleted.
     *
     * @param int|null $id
     * @return void
     */
    public function deleteArticle(?int $id = null): void
    {
        if (!isset($id)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            ArticleModel::removeArticle(id: $id); // Delete article
            foreach ((array)scandir('assets/uploads/articles') as $file) { // Remove all images associated with it
                if (str_starts_with($file, $id . '_')) { // if the file starts with the article ID remove it
                    unlink('assets/uploads/articles/' . $file);
                }
            }

            echo json_encode(['success' => 'articleDelete']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Delete a single image from an article.
     *
     * Removes the specified image from the server and updates the database record.
     * Regenerates a thumbnail if necessary or adds a placeholder when no images remain.
     *
     * @param int|null $id
     * @param string|null $img
     * @return void
     */
    public function deleteArticleImage(?int $id = null, ?string $img = null): void
    {
        if (!isset($id) or !isset($img)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            $img = substr($img, 1); // Get image path without '/' on the beginning
            $imgId = preg_match('/\/(\d+_\d+)(?:_thumbnail)?\.jpeg$/', $img, $matches) ? $matches[1] : null; // Get img id (35_0, 21_2, ...)


            // Get image paths from server
            $imagePaths = explode(',', ArticleModel::selectArticle(conditions: 'WHERE id = ' . $id)['image_paths']);

            // Get all images that contain img id
            $imagePathsToRemove = array_filter($imagePaths, function ($item) use ($imgId) {
                return str_contains($item, $imgId);
            });

            // Remove files from server
            foreach ($imagePathsToRemove as $imagePath) {
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Create different array where are not removed images
            $newImagePaths = array_values(array_diff($imagePaths, $imagePathsToRemove));

            // If new imagepath is empty fill it with 'null' because of the DB
            if (count($newImagePaths) === 0) {
                $newImagePaths = null;
            }

            $thumbnailPath = $this->generateThumbnailIfNoneIsPresent($newImagePaths);
            if ($thumbnailPath) {
                $newImagePaths[] = $thumbnailPath;
            }

            // Update data in DB
            ArticleModel::updateArticle(id: $_GET['id'], imagePaths: $newImagePaths ?? ['null']);

            echo json_encode(['success' => 'imageDelete']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Generate a thumbnail if none is present
     *
     * Generates a thumbnail if necessary.
     *
     * @param array<string, string>|null $imagePaths
     * @return string|null
     * @throws Exception If deleting the image or updating the database fails
     */
    private function generateThumbnailIfNoneIsPresent(?array $imagePaths): string|null
    {
        if (!$imagePaths) {
            return null;
        }

        $thumbnail = '_thumbnail.jpeg';
        $hasThumbnail = !empty(array_filter($imagePaths, fn($str) => str_contains($str, $thumbnail))); // Check if thumbnail is present

        if (!$hasThumbnail and count($imagePaths) > 0) {
            $thumbnailPath = str_replace('.jpeg', $thumbnail, $imagePaths[0]);
            ImageHelper::generateThumbnail(
                image: imagecreatefromjpeg($imagePaths[0]),
                imagePath: $thumbnailPath,
            );

            return $thumbnailPath;
        }

        return null;
    }
}
