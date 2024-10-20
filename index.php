<?php
    declare(strict_types=1);

    namespace src;

    // Initialize config file
    require 'config.php';
    require_once 'src/loader.php';

    use Controllers\ErrorController;
    use Controllers\HomepageController;
    use Controllers\UserController;
    use Controllers\LoginController;
    use Controllers\RegisterController;
    use Controllers\NewsController;
    use Exception;
    use Logic\Router;
    use Models\DatabaseConnector;

    DatabaseConnector::init();

    // Session & get urls
    $url = $_SERVER['REQUEST_URI'];
    $path = parse_url(url: $url, component:  PHP_URL_PATH); // Get path
    $query = parse_url(url: $url, component:  PHP_URL_QUERY); // Get queries
    $controller = null;

    // Get path
    $path = (str_contains(haystack: $path, needle: '/~vanekeri')) ?
        str_replace(search: '/~vanekeri', replace: '', subject: $_SERVER['REQUEST_URI']) : $path; // remove subpage
    $path = strtok(string: $path, token: '?'); // strip path of arguments

    // Routing
    try {
        switch ($path) {
            case '/' :
                $controller = new HomepageController();
                break;

            case '/user' :
                $controller = new UserController();

                if (!isset($_SESSION['user'])) {
                    Router::redirect('login');
                }

                break;

            case '/login' :
                $controller = new LoginController();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->login();
                }
                break;

            case '/register' :
                $controller = new RegisterController();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->register();
                }
                break;

            case '/news':
                $controller = new NewsController();
                break;

            default:
                http_response_code(404);
                $controller = new ErrorController();
                break;
        }

        // Render webpage content
        require 'src/Views/Templates/header.php'; // head
        $controller->render(); // main content
        require 'src/Views/Templates/footer.php'; // foot

    } catch (Exception $e) {
        $error_msg = 'Server Error 500. An error has occurred during the webpage rendering - redirecting back to the homepage. Error message: '. $e->getMessage();
        Router::redirect(path: '', query: 'error', parameters: $error_msg, responseCode: 500);
    }