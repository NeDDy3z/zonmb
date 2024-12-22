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
     * @var Validator $validator The validator instance for validating article data
     */
    private Validator $validator;

    /**
     * Constructor
     *
     * Initializes the controller based on the provided action.
     * Handles routing and checks for user privileges for certain actions.
     *
     * @param string|null $action The action to be performed (e.g., 'get', 'add', 'edit', 'delete', etc.)
     */
    public function __construct(?string $action = '')
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $this->validator = new Validator();
        $this->action = $action ?? '';

        switch ($this->action) {
            case 'get':
                $this->getArticles();
                break;
            case 'add':
            case 'edit':
                $privilegeRedirect->redirectUser();
                $this->page = $this->editorPage;
                break;
            case 'delete':
                $privilegeRedirect->redirectUser();
                if (!isset($_GET['id'])) {
                    echo json_encode(['error' => 'missingID']);
                    exit();
                } else {
                    if (isset($_GET['img'])) {
                        $this->deleteArticleImage();
                    } else {
                        $this->deleteArticle();
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * Render the appropriate webpage based on the action.
     *
     * Handles redirection or rendering of article content or the editor page
     * based on the action and provided data.
     *
     * @throws Exception If any error occurs during rendering
     * @return void
     */
    public function render(): void
    {
        switch ($this->action) {
            case null:
                Router::redirect(path: 'news', query: ['error' => 'articleNotFound']);
                break;
            case 'add':
            case 'edit':
                if ($_GET['id']) {
                    $article = Article::getArticleById($_GET['id']);
                }
                $this->page = $this->editorPage;
                break;
            case 'delete':
                break;
            default: $article = Article::getArticleBySlug($this->action);
        };

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
     * @return void
     */
    public function getArticles(): void
    {
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $sortDirection = $_GET['sortDirection'] ?? null;
        $page = $_GET['page'] ?? 1;

        // Convert date format
        $search = DateHelper::ifPrettyConvertToISO($search);

        // Create query
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
     * Add a new article to the database.
     *
     * Validates the provided article data and saves it along with its associated images.
     * Redirects to the created article upon success.
     *
     * @throws Exception If validation or saving data fails
     * @return void
     */
    public function addArticle(): void
    {
        try {
            // Get data from $_POST
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $content = $_POST['content'] ?? null;
            $author = $_POST['author'] ?? null;
            $images = ImageHelper::getUsableImageArray($_FILES['images']) ?? null;

            $this->validator->validateArticle(
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
                    // Generate thumbnail from first image
                    if ($i === 0) {
                        $thumbnailPath = 'assets/uploads/articles/' . $articleId . '_0_thumbnail.jpeg';
                        $imagePaths[] = $thumbnailPath;
                        ImageHelper::saveImage(
                            image: ImageHelper::resize(ImageHelper::processArticleImage($images[$i]), 300, 200),
                            imagePath: $thumbnailPath,
                        );
                    }

                    // Save image
                    $imagePath = 'assets/uploads/articles/' . $articleId . '_' . $i .'.jpeg';
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
            Router::redirect(path: 'articles/add', query: ['error' => 'articleAddError', 'errorDetails' => $e->getMessage()]);
        }
    }

    /**
     * Edit an existing article in the database.
     *
     * Allows modification of article data and the addition of new images while
     * preserving or updating existing ones.
     *
     * @throws Exception If validation or saving data fails
     * @return void
     */
    public function editArticle(): void
    {
        try {
            $id = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $content = $_POST['content'] ?? null;
            $images = ImageHelper::getUsableImageArray($_FILES['images']) ?? null;

            if ($images[0]['tmp_name'] === "") {
                unset($images);
            }

            // Check titles etc..
            $this->validator->validateArticle(
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

            try {
                if (isset($images)) {
                    $imagePaths = explode(',', ArticleModel::selectArticle(conditions: 'WHERE id = '. $id)['image_paths']);
                    $lastImageId = 0;

                    // Get last img id and add 1 to it
                    foreach ((array)scandir('assets/uploads/articles') as $file) {
                        if (str_starts_with((string)$file, $id . '_') and (int)explode('_', (string)$file)[1] > $lastImageId) {
                            $lastImageId = (int)explode('_', (string)$file)[1];
                        }
                    }

                    $lastImageId++;

                    for ($i = 0; $i < count($images); $i++) {
                        $imagePath = 'assets/uploads/articles/' . $id . '_' . $lastImageId .'.jpeg';
                        $imagePaths[] = $imagePath; // Add to array

                        // Save image
                        ImageHelper::saveImage(
                            image: ImageHelper::processArticleImage($images[$i]),
                            imagePath: $imagePath,
                        );

                        $lastImageId++;
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
                Router::redirect(path: 'articles/edit', query: ['id' => $id, 'error' => 'articleEditError', 'errorDetails' => $e->getMessage()]);
            }
        } catch (Exception $e) {
            Router::redirect(path: 'articles/edit', query: ['error' => 'articleEditError', 'errorDetails' => $e->getMessage()]);
        }
    }

    /**
     * Delete an article and all its associated images.
     *
     * Ensures that all images related to the article are removed from the server
     * when the article is deleted.
     *
     * @throws Exception If deleting data fails
     * @return void
     */
    public function deleteArticle(): void
    {
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            ArticleModel::removeArticle(id: $_GET['id']); // Delete article
            foreach ((array)scandir('assets/uploads/articles') as $file) { // Remove all images associated with it
                if (str_starts_with($file, $_GET['id'] . '_')) { // if the file starts with the article ID remove it
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
     * @throws Exception If deleting the image or updating the database fails
     * @return void
     */
    public function deleteArticleImage(): void
    {
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            $img = substr($_GET['img'], 1); // Get image path without '/' on the beginning
            $imgId = preg_match('/\/(\d+_\d+)(?:_thumbnail)?\.jpeg$/', $img, $matches) ? $matches[1] : null; // Get img id (35_0, 21_2, ...)


            // Get image paths from server
            $imagePaths = explode(',', ArticleModel::selectArticle(conditions: 'WHERE id = '. $_GET['id'])['image_paths']);

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
            $thumbnail = '_thumbnail.jpeg';
            $hasThumbnail = !empty(array_filter($newImagePaths, fn($str) => str_contains($str, $thumbnail))); // Check if thumbnail is present

            // If new imagepath is empty fill it with 'null' because of the DB
            if (count($newImagePaths) === 0) {
                $newImagePaths = ['null'];
            } elseif (!$hasThumbnail and count($newImagePaths) > 0) {
                $thumbnailPath = str_replace('.jpeg', $thumbnail, $newImagePaths[0]);
                ImageHelper::generateThumbnail(
                    image: imagecreatefromjpeg($newImagePaths[0]),
                    imagePath: $thumbnailPath,
                );

                $newImagePaths[] = $thumbnailPath;
            }

            // Update data in DB
            ArticleModel::updateArticle(id: $_GET['id'], imagePaths: $newImagePaths);

            echo json_encode(['success' => 'imageDelete']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

}
