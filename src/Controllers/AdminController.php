<?php

namespace Controllers;

use Helpers\PrivilegeRedirect;
use Logic\Article;
use Logic\DatabaseException;
use Logic\User;
use Models\ArticleModel;
use Models\UserModel;

/**
 * AdminController
 *
 * The AdminController class handles functionality for the admin panel. It ensures only privileged users can access
 * the admin area and provides tools for managing users and articles, including fetching their data from the database
 * for rendering the admin dashboard.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class AdminController extends Controller
{
    /**
     * @var string $page Path to the admin dashboard view
     */
    private string $page = ROOT . 'src/Views/admin.php';

    /**
     * Constructor
     *
     * Ensures that only logged-in users with appropriate privileges can access the admin functionalities.
     * Redirects users without proper permissions to the appropriate page.
     */
    public function __construct()
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectEditor();
    }

    /**
     * Render the admin dashboard page.
     *
     * Fetches and prepares the list of users and articles for display in the admin panel.
     *
     * @throws DatabaseException If any error occurs while retrieving data from the database
     * @return void
     */
    public function render(): void
    {
        $pageUsers = (isset($_GET['page-users'])) ? (int)$_GET['page-users'] : 1;
        $pageArticles = (isset($_GET['page-articles'])) ? (int)$_GET['page-articles'] : 1;

        $users = $this->loadUsers();
        $articles = $this->loadArticles();

        require_once $this->page; // Load page content
    }

    /**
     * Load users from the database.
     *
     * Fetches user data from the database, creates `User` objects, and handles invalid profile image paths by
     * replacing them with the default image path.
     *
     * @return array<User> A list of `User` objects representing all users in the system
     * @throws DatabaseException If an error occurs during the database query
     */
    private function loadUsers(): array
    {
        $databaseUsers = UserModel::selectUsers();
        $users = [];

        foreach ($databaseUsers as $user) {
            $users[] = new User(
                id: (int)$user['id'],
                username: $user['username'],
                fullname: $user['fullname'],
                image: file_exists($user['profile_image_path']) ? $user['profile_image_path'] : DEFAULT_PFP,
                role: $user['role'],
                createdAt: $user['created_at'],
            );
        }

        return $users;
    }

    /**
     * Load articles from the database.
     *
     * Retrieves article data from the database and creates `Article` objects. Handles the processing of comma-separated
     * image paths into an array and includes all article details necessary for display and management.
     *
     * @return array<Article> A list of `Article` objects representing all articles in the system
     * @throws DatabaseException If an error occurs during the database query
     */
    private function loadArticles(): array
    {
        $databaseArticles = ArticleModel::selectArticles();
        $articles = [];

        $imagePaths = isset($articleData['image_paths']) ? explode(',', $articleData['image_paths']) : null;

        foreach ($databaseArticles as $article) {
            $articles[] = new Article(
                id: (int)$article['id'],
                title: $article['title'],
                subtitle: $article['subtitle'],
                content: $article['content'],
                slug: $article['slug'],
                imagePaths: $imagePaths,
                authorId: (int)$article['author_id'],
                createdAt: $article['created_at'],
            );
        }

        return $articles;
    }
}
