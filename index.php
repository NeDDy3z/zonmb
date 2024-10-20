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
    $path = strval(parse_url(url: $url, component:  PHP_URL_PATH)); // Get path
    $query = parse_url(url: $url, component:  PHP_URL_QUERY); // Get queries
    $controller = null;

    // Get path
    $path = (str_contains(haystack: $path, needle: '/~vanekeri')) ? str_replace(search: '/~vanekeri', replace: '', subject: $_SERVER['REQUEST_URI']) : $path; // remove subpage
    $path = strtok(string: $path, token: '?'); // strip path of arguments

    // Routing
    session_start();
    try {
        switch ($path) {
            case '/' :
                $title = 'ZONMB';
                $controller = new HomepageController();
                break;

            case '/user' :
                $title = 'ZONMB - Uživatel';
                $controller = new UserController();

                if (!isset($_SESSION['user'])) {
                    Router::redirect('login');
                }

                break;

            case '/login' :
                $title = 'ZONMB - Přihlášení';
                $controller = new LoginController();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->login();
                }
                break;

            case '/logout':
                session_unset();
                session_destroy();

                Router::redirect('', 'popup', 'Odhlášení proběhlo úspěšně');

            case '/register' :
                $title = 'ZONMB - Registrace';
                $controller = new RegisterController();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->register();
                }
                break;

            case '/news':
                $title = 'ZONMB - Novinky';
                $controller = new NewsController();
                break;

            default:
                $title = 'ZONMB - Chyba';
                http_response_code(404);
                $controller = new ErrorController();
                break;
        }

        // Set global title
        global $title;

        // Render webpage content
        require_once 'src/Views/Templates/header.php'; // head
        $controller->render(); // main content
        require_once 'src/Views/Templates/footer.php'; // foot

    } catch (Exception $e) {
        $error_msg = 'Server Error 500. An error has occurred during the webpage rendering - redirecting back to the homepage. Error message: '. $e->getMessage();
        Router::redirect(path: '', query: 'error', parameters: $error_msg, responseCode: 500);
    }