<?php

declare(strict_types=1);

namespace Logic;

use Controllers\AdminController;
use Controllers\ArticleController;
use Controllers\CommentController;
use Controllers\Controller;
use Controllers\ErrorController;
use Controllers\HomepageController;
use Controllers\LoginController;
use Controllers\NewsController;
use Controllers\RegisterController;
use Controllers\UserController;
use Exception;

/**
 * Router
 *
 * The `Router` class manages request routing for the application. It determines the appropriate
 * controller and method to handle incoming HTTP requests, processes both `GET` and `POST` requests,
 * and supports redirection to other paths with optional query parameters.
 *
 * @package Logic
 * @author Erik Vaněk
 */
class Router
{
    /**
     * All routes with specific commands for GET method
     *
     * @var array|array[]
     */
    private static array $getRoutes = [
        'error' => [
            'class' => ErrorController::class,
            'action' => 'render',
        ],
        '' => [
            'class' => HomepageController::class,
            'action' => 'render',
        ],

        'login' => [
            'class' => LoginController::class,
            'action' => 'render',
        ],
        'logout' => [
            'class' => LoginController::class,
            'action' => 'logout',
        ],
        'register' => [
            'class' => RegisterController::class,
            'action' => 'render',
        ],

        'user' => [ // this includes user-edit page
            'class' => UserController::class,
            'action' => 'render',
        ],
        'user/edit' => [
            'class' => UserController::class,
            'action' => 'renderEditor',
        ],
        'user/me' => [
            'class' => UserController::class,
            'action' => 'getMe',
        ],
        'user/exists' => [
            'class' => UserController::class,
            'action' => 'existsUsername',
        ],
        'user/delete' => [
            'class' => UserController::class,
            'action' => 'delete',
        ],

        'news' => [
            'class' => NewsController::class,
            'action' => 'render',
        ],
        'article' => [ // this includes article/add article/edit
            'class' => ArticleController::class,
            'action' => 'render',
        ],
        'article/add' => [
            'class' => ArticleController::class,
            'action' => 'renderEditor',
        ],
        'article/edit' => [
            'class' => ArticleController::class,
            'action' => 'renderEditor',
        ],
        'article/exists' => [
            'class' => ArticleController::class,
            'action' => 'exists',
        ],
        'article/delete' => [
            'class' => ArticleController::class,
            'action' => 'delete',
        ],

        'comment/delete' => [
            'class' => CommentController::class,
            'action' => 'delete',
        ],

        'admin' => [
            'class' => AdminController::class,
            'action' => 'render',
        ],

        'users' => [
            'class' => UserController::class,
            'action' => 'getUsers',
        ],
        'articles' => [
            'class' => ArticleController::class,
            'action' => 'getArticles',
        ],
        'comments' => [
            'class' => CommentController::class,
            'action' => 'getComments',
        ],
    ];

    /**
     * All routes with specific commands for POST method
     *
     * @var array|array[]
     */
    private static array $postRoutes = [
        'login' => [
            'class' => LoginController::class,
            'action' => 'login',
        ],
        'register' => [
            'class' => RegisterController::class,
            'action' => 'register',
        ],
        'user/edit' => [
            'class' => UserController::class,
            'action' => 'edit',
        ],
        'user/edit/fullname' => [
            'class' => UserController::class,
            'action' => 'editFullname',
        ],
        'user/edit/image' => [
            'class' => UserController::class,
            'action' => 'editImage',
        ],
        'user/edit/password' => [
            'class' => UserController::class,
            'action' => 'editPassword',
        ],
        'article/add' => [
            'class' => ArticleController::class,
            'action' => 'add',
        ],
        'article/edit' => [
            'class' => ArticleController::class,
            'action' => 'edit',
        ],
        'comment/add' => [
            'class' => CommentController::class,
            'action' => 'add',
        ],
    ];


    /**
     * Redirects the user to a specific path, optionally appending query parameters.
     *
     * A response header is set, and the script is terminated after redirection.
     *
     * @param string $path The path to redirect to (relative to the base URL).
     * @param array<string, string>|null $query Optional query parameters for the URL.
     * @param int $responseCode The HTTP response code for the redirection (default: `200`).
     *
     * @return void
     */
    public static function redirect(string $path, ?array $query = null, int $responseCode = 200): void
    {
        // Avoid headers already sent error
        if (!headers_sent()) {
            ob_start();
        }

        // Build a query
        $resultQuery = [];
        if ($query) {
            foreach ($query as $key => $value) {
                $resultQuery[] = $key . '=' . $value;
            }
        }

        $resultQuery = empty($resultQuery) ? '' : '?' . implode('&', $resultQuery);

        // Redirect
        http_response_code($responseCode);
        header('location: ' . BASE_URL . '/' . $path . $resultQuery);
        exit();
    }

    /**
     * Routes an HTTP request to the appropriate controller and method based on the URL and method.
     *
     * Handles `GET` and `POST` requests, with a fallback to render a 405 error
     * if the HTTP method is unsupported.
     *
     * @param string $url The requested URL, relative to the base URL.
     * @param string $method The HTTP method used for the request (e.g., `GET`, `POST`).
     *
     * @return void
     *
     * @throws Exception If unable to route the request or if an error occurs.
     */
    public static function route(string $url, string $method): void
    {
        // Remove trailing slash
        if (str_ends_with($url, '/')) {
            self::redirect(rtrim($url, '/'));
        }

        match ($method) {
            'GET' => self::GET($url),
            'POST' => self::POST($url),
            default => (new ErrorController(405))->render(),
        };
    }

    /**
     * Rewrite incoming url into a specified format.
     * For example .../article/article-one will be rewritten into .../article?slug=article-one
     *
     * @param string $url
     * @return string
     */
    private static function rewrite(string $url): string
    {
        switch (true) {
            // Case for article page, if url contains article slug that is more than 10 characters long, convert the url into article?slug=<article_slug>
            case preg_match('#^article/(.+)#', $url, $matches) and strlen($matches[1]) >= 10:
                $_GET['slug'] = $matches[1];
                return 'article';
            default:
                return $url;
        }
    }

    /**
     * Render header and footer views where in between is a page content.
     *
     * @param callable $callback
     * @return void
     */
    private static function includeHeaderAndFooter(callable $callback): void
    {
        require_once ROOT . 'src/Views/Partials/header.php'; // Include the header
        $callback(); // Execute the render action
        require_once ROOT . 'src/Views/Partials/footer.php'; // Include the footer
    }

    /**
     * Handle `GET` requests and route them to the appropriate controller.
     *
     * Routes are determined based on URL segments, with specific controllers
     * for predefined paths (e.g., `admin`, `news`). If no matching controller is found,
     * an error (404) controller is shown.
     *
     * Headers and footers for the view are included automatically.
     *
     * @param string $url The requested URL (e.g., `/admin`, `/articles/123`).
     *
     * @return void
     * @throws Exception
     */
    private static function GET(string $url): void
    {
        $url = self::rewrite($url);
        $route = self::$getRoutes[$url] ?? null;

        if (isset($route)) {
            /** @var Controller $controller */
            $controller = new $route['class']();
            $action = $route['action'] ?? null;

            // Check if method exists in controller and execute it
            if (method_exists($controller, $action)) {
                // On render action also render header and footer

                if (str_contains(haystack: $action, needle: 'render')) {
                    self::includeHeaderAndFooter(fn() => $controller->$action());
                    return;
                }

                // Execute action
                $controller->$action();
            } else {
                self::redirect('error', ['error' => 'Bad request'], 404);
            }
        } else {
            self::redirect('error', ['error' => 'Bad request'], 404);
        }
    }

    /**
     * Handle `POST` requests and route them to the appropriate controller's method.
     *
     * Matches specific path segments to defined methods in applicable controllers.
     * If no match is found, an error controller is rendered.
     *
     * @param string $url The requested URL (e.g., `/login`, `/articles/add`).
     *
     * @return void
     *
     * @throws Exception If routing fails or the method is not implemented.
     * @throws DatabaseException If a database-related error occurs.
     */
    private static function POST(string $url): void
    {
        $route = self::$postRoutes[$url] ?? null;

        if (isset($route)) {
            /** @var Controller $controller */
            $controller = new $route['class']();
            $action = $route['action'];

            // Check if method exists in controller and execute it
            if (method_exists($controller, $action)) {
                $controller->$action();
            }
        } else {
            // Error on incorrect request
            self::redirect('error', ['error' => 'Bad request'], 404);
        }
    }
}
