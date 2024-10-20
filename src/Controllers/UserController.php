<?php

namespace Controllers;

class UserController implements IController {

    private string $page = 'src/Views/user.php'; // Import page content

    // Render user page
    public function render(): void {
        require_once $this->page; // Load page content
    }

    // Logout function
    public function logout(): void
    {
        session_destroy(); // Destroy session
        session_unset();

        Router::redirect(path: '', query: 'success', parameters: 'Odhlášení proběhlo úspěšně');
    }
}
