<?php

namespace Zonmb\Controllers;

use Zonmb\Logic\Router;

class UserController {

    private string $page = 'src/Views/user.php'; // Import page content

    public function render(): void {
        require_once $this->page; // Load page content
    }

    // Logout function
    public function logout(): void
    {
        session_unset();
        session_destroy();

        Router::redirect('', 'popup', 'Odhlášení proběhlo úspěšně');
    }
}
