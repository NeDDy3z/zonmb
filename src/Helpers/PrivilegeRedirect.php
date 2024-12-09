<?php

namespace Helpers;

use Logic\Router;
use Logic\User;

class PrivilegeRedirect
{
    /** @var User|null $user */
    private ?User $user;

    /**
     * Set user from $_SESSION
     */
    public function __construct()
    {
        $this->user = $_SESSION['user_data'] ?? null;
    }

    /**
     * If user isn't User/Editor/Admin redirect
     * @return void
     */
    public function redirectHost(): void
    {
        if ($this->user === null) {
            Router::redirect(path: 'login', query: ['error' => 'notLoggedIn']);
        }
    }

    /**
     * If user isn't Editor/Admin redirect
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
     * If user isn't Admin redirect
     * @return void
     */
    public function redirectEditor(): void
    {
        $this->redirectHost();
        if (isset($this->user) and !$this->user->isAdmin()) {
            Router::redirect(path: 'login', query: ['error' => 'notAuthorized']);
        }
    }

}
