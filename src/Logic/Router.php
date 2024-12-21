<?php

declare(strict_types=1);

namespace Logic;

use Controllers\AdminController;
use Controllers\ArticleController;
use Controllers\ErrorController;
use Controllers\HomepageController;
use Controllers\LoginController;
use Controllers\NewsController;
use Controllers\RegisterController;
use Controllers\SearchController;
use Controllers\TestingController;
use Controllers\UserController;
use Exception;

class Router
{
    /**
     * Redirect to correct path and/or with query
     * @param string $path
     * @param array<string, string> $query
     * @param int $responseCode
     * @return void
     */
    public static function redirect(string $path, ?array $query = null, int $responseCode = 200): void
    {
        $resultQuery = [];
        if ($query) {
            foreach ($query as $key => $value) {
                $resultQuery[] = $key . '=' . $value;
            }
        }

        $resultQuery = empty($resultQuery) ? '' : '?' . implode('&', $resultQuery);

        http_response_code($responseCode);
        header(header: ('location: '. BASE_URL .'/' . $path . $resultQuery));
        exit();
    }

    /**
     * Route to correct controller
     * @param string $url
     * @param string $method
     * @return void
     * @throws Exception
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
     * @param string $url
     * @return void
     */
    private static function GET(string $url): void
    {
        $url = explode('/', $url);
        $controller = match ($url[0]) {
            '' => new HomepageController(),
            'admin' => new AdminController(),
            'articles' => new ArticleController($url[1] ?? null),
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
     * Take care of POST requests
     * @param string $url
     * @throws Exception
     * @throws DatabaseException
     */
    private static function POST(string $url): void
    {
        match ($url) {
            'login' => (new LoginController())->login(),
            'register' => (new RegisterController())->register(),
            'articles/add' => (new ArticleController())->addArticle(),
            'articles/edit' => (new ArticleController())->editArticle(),
            'testing/image-upload' => (new TestingController())->testImageUpload(),
            'users/edit' => (new UserController())->updateUser(),
            'users/edit/fullname' => (new UserController())->updateFullname(),
            'users/edit/profile-image' => (new UserController())->updateProfileImage(),
            default => (new ErrorController())->render(),
        };
    }
}
