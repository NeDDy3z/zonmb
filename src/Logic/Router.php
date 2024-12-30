<?php

declare(strict_types=1);

namespace Logic;

use Controllers\AdminController;
use Controllers\ArticleController;
use Controllers\CommentController;
use Controllers\ErrorController;
use Controllers\HomepageController;
use Controllers\LoginController;
use Controllers\NewsController;
use Controllers\RegisterController;
use Controllers\TestingController;
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
        header('location: '. BASE_URL .'/' . $path . $resultQuery);
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
        $url = explode('/', $url);
        $controller = match ($url[0]) {
            '' => new HomepageController(),
            'admin' => new AdminController(),
            'articles' => new ArticleController($url[1] ?? null),
            'comments' => new CommentController($url[1] ?? null),
            'login' => new LoginController(),
            'logout' => (new UserController())->logout(),
            'news' => new NewsController(),
            'register' => new RegisterController(),
            'testing' => new TestingController($url[1] ?? null),
            'users' => new UserController($url[1] ?? null),
            default => new ErrorController(404),
        };

        $controller ??= new ErrorController(404);

        require_once ROOT .'src/Views/Partials/header.php'; // head
        $controller->render();
        require_once ROOT .'src/Views/Partials/footer.php'; // foot
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
        match ($url) {
            'login' => (new LoginController())->login(),
            'register' => (new RegisterController())->register(),
            'articles/add' => (new ArticleController())->addArticle(),
            'articles/edit' => (new ArticleController())->editArticle(),
            'comments/add' => (new CommentController('add'))->addComment(),
            'testing/image-upload' => (new TestingController())->testImageUpload(),
            'users/edit' => (new UserController())->updateUser(),
            'users/edit/fullname' => (new UserController())->updateFullname(),
            'users/edit/image' => (new UserController())->updateProfileImage(),
            'users/edit/password' => (new UserController())->updatePassword(),
            default => (new ErrorController())->render(),
        };
    }
}
