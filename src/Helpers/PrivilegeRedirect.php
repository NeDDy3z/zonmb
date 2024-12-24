<?php

namespace Helpers;

use Logic\Router;
use Logic\User;

/**
 * PrivilegeRedirect
 *
 * This helper class provides methods to enforce access control by redirecting users
 * based on their privilege levels. It verifies whether a user has the required access
 * rights (User, Editor, Admin) and redirects unauthorized users to a specified page.
 *
 * @package Helpers
 */
class PrivilegeRedirect
{
    /** @var User|null $user The currently authenticated user */
    private ?User $user;

    /**
     * Constructor
     *
     * Initializes the object by setting the user data from the session.
     * If no user data is present in the session, the `$user` property is set to `null`.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = $_SESSION['user_data'] ?? null;
    }

    /**
     * Redirect users who are not authenticated or lack privileges.
     *
     * If the user is not authenticated (i.e., `$user` is `null`), they are redirected to
     * the login page. Optionally, this method has a parameter for a custom query string.
     *
     * @param array<string, string>|null $query
     * @return void
     */
    public function redirectHost(?array $query = ['error' => 'notAuthorized']): void
    {
        if (!isset($this->user)) {
            Router::redirect(path: 'login', query: $query);
        }
    }

    /**
     * Redirect users who are not at least Editors.
     *
     * This method ensures the user is authenticated and has Editor or higher privileges.
     * If the user is unauthorized, they will be redirected to the login page with an appropriate
     * query string error.
     *
     * @return void
     */
    public function redirectUser(): void
    {
        $this->redirectHost();
        if (isset($this->user) and !$this->user->isEditor()) {
            Router::redirect(path: 'login', query: ['error' => 'notAuthorized']);
        }
    }

    /**
     * Redirect users who are not Admins.
     *
     * This method ensures the user is authenticated and has Admin privileges.
     * If the user is unauthorized, they will be redirected to the login page with an appropriate
     * query string error.
     *
     * @return void
     */
    public function redirectEditor(): void
    {
        $this->redirectHost();
        if (isset($this->user) and !$this->user->isAdmin()) {
            Router::redirect(path: 'login', query: ['error' => 'notAuthorized']);
        }
    }

    public function redirectAdmin(): void
    {
        $this->redirectHost();
        if (isset($this->user) and $this->user->getRole() !== 'owner') {
            Router::redirect(path: 'login', query: ['error' => 'notAuthorized']);
        }
    }
}
