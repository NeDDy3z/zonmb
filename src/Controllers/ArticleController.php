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
     * @var PrivilegeRedirect $privilegeRedirect The privilege redirect instance for redirecting users
     */
    private PrivilegeRedirect $privilegeRedirect;

    /**
     * @var Validator $validator The validator instance for validating article data
     */
    private Validator $validator;

    /**
     * Constructor
     *
     * Initializes the controller.
     */
    public function __construct()
    {
        $this->privilegeRedirect = new PrivilegeRedirect();
        $this->validator = new Validator();
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
        $slug = $_GET['slug'] ?? null;

        try {
            $article = Article::get(slug: $slug);
        } catch (Exception $e) {
            Router::redirect(path: 'error', query: ['error' => 'articleNotFound']);
        }

        require_once $this->page; // Load page content
    }

    /**
     * Render the appropriate webpage based on the action.
     *
     * Handles rendering of the user editor.
     * Ensures the data loaded corresponds to the currently edited user.
     *
     * @return void
     * @throws Exception If a user is not authorized or data fails to load.
     */
    public function renderEditor(): void
    {
        $this->privilegeRedirect->redirectUser();
        $type = (str_contains(haystack: $_SERVER['REQUEST_URI'], needle: 'add')) ? 'add' : 'edit';
        $id = $_GET['id'] ?? null;

        // On editing require article ID
        if ($type === 'edit') {
            if (!isset($id)) {
                Router::redirect(path: 'article/add', query: ['error' => 'missingID']);
            }

            // Get article from DB using ID
            $article = Article::get(id: $id);

            if (!isset($article)) {
                Router::redirect(path: 'article/add', query: ['error' => 'incorrectID']);
            }
        }

        require_once $this->editorPage; // Load page content
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
        try {
            $search = $_GET['search'] ?? null;
            $sort = $_GET['sort'] ?? null;
            $sortDirection = $_GET['sortDirection'] ?? null;
            (int)$page = $_GET['page'] ?? 1;

            // Convert date format
            $search = DateHelper::ifPrettyConvertToISO($search);

            // Create a query
            $conditions = ($search) ? "WHERE id like '$search%' or title LIKE '%$search%' OR subtitle LIKE '%$search%' OR content LIKE '%$search%' OR created_at LIKE '%$search%'" : "";
            $conditions .= ($sort) ? " ORDER BY $sort" : "";
            $conditions .= ($sortDirection) ? " $sortDirection" : "";
            $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

            $articlesData = ArticleModel::selectArticles(
                conditions: $conditions,
            );

            echo json_encode($articlesData);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * Check if an article title exists in the database.
     *
     * @return void
     */
    public function exists(): void
    {
        $this->privilegeRedirect->redirectUser();

        try {
            $id = $_GET['id'] ?? null;
            $title = $_GET['title'] ?? null;

            if (!isset($title)) {
                echo json_encode(['error' => 'missingTitle']);
                exit();
            }

            // Check if ID is provided, if so, check if the provided title matches the title under the ID from database
            if (isset($id)) {
                /** @var Article $article */
                $article = Article::get(id: $id);

                if ($article->getTitle() !== $title) {
                    echo json_encode(['exists' => true]);
                }
            } else {
                $exists = ArticleModel::existsArticle(title: $title);
                echo json_encode(['exists' => $exists]);
            }

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
    public function add(): void
    {
        $this->privilegeRedirect->redirectUser();

        try {
            // Get data from $_POST
            (int)$author = $_POST['author'] ?? null;
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


            Router::redirect(path: "article/$slug", query: ['success' => 'articleAdded']);
        } catch (Exception $e) {
            Router::redirect(path: 'article/add', query: ['error' => $e->getMessage()]);
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
    public function edit(): void
    {
        $this->privilegeRedirect->redirectUser();

        try {
            (int)$id = $_POST['id'] ?? null;
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

                if ($imagePaths[0] === '') {
                    $imagePaths = null;
                }
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

                Router::redirect(path: "article/$slug", query: ['success' => 'articleEdited']);
            } catch (Exception $e) {
                Router::redirect(path: "article/edit", query: ['id' => $id, 'error' => $e->getMessage()]);
            }
        } catch (Exception $e) {
            Router::redirect(path: "article/edit", query: ['id' => $id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Delete an article and all its associated images.
     *
     * Ensures that all images related to the article are removed from the server
     * when the article is deleted.
     *
     * Additionally, this method calls for another method if $_GET request contains an image query
     *
     * @return void
     */
    public function delete(): void
    {
        $this->privilegeRedirect->redirectUser();

        try {
            $id = $_GET['id'] ?? null;
            $image = $_GET['image'] ?? null;

            if (!isset($id)) {
                echo json_encode(['error' => 'missingID']);
                exit();
            }

            if (isset($image)) {
                $this->deleteImage($id, $image);
                exit();
            }

            $article = Article::get(id: $id);

            if (!isset($article)) {
                echo json_encode(['error' => 'incorrectID']);
                exit();
            }

            ArticleModel::removeArticle(id: $id); // Delete article

            foreach ((array)scandir('assets/uploads/articles') as $file) { // Remove all images associated with it
                if (str_starts_with($file, $id . '_')) { // if the file starts with the article ID remove it
                    unlink('assets/uploads/articles/' . $file);
                }
            }

            echo json_encode(['success' => 'articleDeleted']);
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
    private function deleteImage(?int $id = null, ?string $img = null): void
    {
        try {
            if (!isset($id) or !isset($img)) {
                echo json_encode(['error' => 'missingID']);
                exit();
            }

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

            // Create a different array where are not removed images
            $newImagePaths = array_values(array_diff($imagePaths, $imagePathsToRemove));

            // If new imagepath is empty, fill it with 'null' because of the DB
            if (count($newImagePaths) === 0) {
                $newImagePaths = null;
            }

            $thumbnailPath = $this->generateThumbnailIfNoneIsPresent($newImagePaths);
            if ($thumbnailPath) {
                $newImagePaths[] = $thumbnailPath;
            }

            // Update data in DB
            ArticleModel::updateArticle(id: $_GET['id'], imagePaths: $newImagePaths ?? ['null']);

            echo json_encode(['success' => 'imageDeleted']);
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
