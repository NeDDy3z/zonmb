<?php

namespace Controllers;

class UserController {

    // Render user page
    public function render(): void {
        $title = "ZONMB - Uživatel";

        require 'src/Views/user.php'; // Import page content
    }

    public function login()
    {

    }

    // Logout function
    public function logout()
    {
        session_destroy(); // Destroy session
        header('Location: /'); // Redirect to home
        exit();
    }
}
