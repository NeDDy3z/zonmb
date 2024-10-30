<?php

declare(strict_types=1);

namespace Logic;

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
     * @param string $path
     * @param string|null $query
     * @param string|null $parameters
     * @param int $responseCode
     * @return void
     */
    public static function redirect(string $path, ?string $query = null, ?string $parameters = null, int $responseCode = 200): void
    {
        $resultQuery = '';
        if ($query && $parameters) {
            $resultQuery = '?' . $query . '=' . urlencode($parameters);
        }

        http_response_code($responseCode);
        header(header: ('location: ./' . $path . $resultQuery));
        exit();
    }

    /**
     * @param string $url
     * @param string $method
     * @return void
     * @throws DatabaseException
     */
    public static function route(string $url, string $method): void
    {
        if ($method === 'GET') {
            self::GET($url);
        } elseif ($method === 'POST') {
            self::POST($url);
        } else {
            (new ErrorController(405))->render();
        }
    }


    /**
     * @param string $url
     * @return void
     */
    private static function GET(string $url): void
    {
        $controller = match ($url) {
            '' => new HomepageController(),
            'user' => new UserController(),
            'login' => new LoginController(),
            'logout' => self::logout(),
            'register' => new RegisterController(),
            'news' => new NewsController(),
            'testing' => new TestingController(),
            default => new ErrorController(404),
        };

        $controller ??= new ErrorController(404);

        require_once '../src/Views/Templates/header.php'; // head
        $controller->render();
        require_once '../src/Views/Templates/footer.php'; // foot

    }

    /**
     * @throws Exception
     * @throws DatabaseException
     */
    private static function POST(string $url): void
    {
        match ($url) {
            'login' => (new LoginController())->login(),
            'register' => (new RegisterController())->register(),
            default => (new ErrorController())->render(),
        };
    }

    /**
     * @return void
     */
    private static function logout(): void
    {
        session_unset();
        session_destroy();
        Router::redirect(path: '', query: 'popup', parameters: 'Odhlášení proběhlo úspěšně');
    }
}
