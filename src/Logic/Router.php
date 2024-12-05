<?php

declare(strict_types=1);

namespace Logic;

use Controllers\ArticleController;
use Controllers\ErrorController;
use Controllers\HomepageController;
use Controllers\LoginController;
use Controllers\NewsController;
use Controllers\RegisterController;
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
        $resultQuery = '';
        if ($query) {
            foreach ($query as $key => $value) {
                $resultQuery .= '&' . $key . '=' . $value;
            }
        }

        http_response_code($responseCode);
        header(header: ('location: '. BASE_URL .'/' . $path . $resultQuery));
        exit();
    }

    /**
     * Route to correct controller
     * @param string $url
     * @param string $method
     * @return void
     * @throws DatabaseException
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
            'PUT' => (new ErrorController(405))->render(),
            default => (new ErrorController(405))->render(),
        };
    }


    /**
     * @param string $url
     * @param string|null $subUrl
     * @return void
     */
    private static function GET(string $url): void
    {
        $controller = match ($url) {
            '' => new HomepageController(),
            'article' => new ArticleController(),
            'article/add' => (new ArticleController())->addArticle(),
            'login' => new LoginController(),
            'logout' => (new UserController())->logout(),
            'news' => new NewsController(),
            'register' => new RegisterController(),
            'testing' => new TestingController(),
            'user' => new UserController(),
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
            'article/add' => (new ArticleController())->addArticle(),
            'testing/image-upload' => (new TestingController())->testImageUpload(),
            'user/profile-image' => (new UserController())->uploadImage(),
            default => (new ErrorController())->render(),
        };
    }
}
