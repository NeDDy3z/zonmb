<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\DatabaseConnector;

class User
{
    private int $id;
    private string $username;
    private string $image;
    private string $role;
    private string $createdAt;


    /**
     * @param int $id
     * @param string $username
     * @param string $image
     * @param string $role
     * @param string $createdAt
     */
    public function __construct(int $id, string $username, string $image, string $role, string $createdAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->image = $image;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }


    /**
     * @param string $username
     * @return User
     * @throws DatabaseException
     * @throws Exception
     */
    public static function getUserByUsername(string $username): User
    {
        $userData = DatabaseConnector::selectUser(username: $username);

        try {
            return new User(
                id: (int)$userData['id'],
                username: $userData['username'],
                image: file_exists($userData['profile_image_path']) ? $userData['profile_image_path'] : DEFAULT_PFP,
                role: $userData['role'],
                createdAt: $userData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst uživatelská data z databáze. ' . $e->getMessage());
        }
    }

    /**
     * Get if user can edit articles
     * @return bool
     */
    public function isEditor(): bool
    {
        if ($this->role === 'editor' || $this->role === 'admin' || $this->role === 'owner') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return void
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
