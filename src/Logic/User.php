<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\DatabaseConnector;
use Models\UserModel;

class User
{
    private int $id;
    private string $username;
    private string $fullname;
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
    public function __construct(int $id, string $username, string $fullname, string $image, string $role, string $createdAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->fullname = $fullname;
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
        try {
            $userData = UserModel::selectUser(username: $username);

            return new User(
                id: (int)$userData['id'],
                username: $userData['username'],
                fullname: $userData['fullname'],
                image: file_exists($userData['profile_image_path']) ? $userData['profile_image_path'] : DEFAULT_PFP,
                role: $userData['role'],
                createdAt: $userData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst uživatelská data z databáze. ' . $e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return User
     * @throws DatabaseException
     */
    public static function getUserById(int $id): User
    {
        try {
            $userData = UserModel::selectUser(id: $id);

            return new User(
                id: (int)$userData['id'],
                username: $userData['username'],
                fullname: $userData['fullname'],
                image: file_exists($userData['profile_image_path']) ? $userData['profile_image_path'] : DEFAULT_PFP,
                role: $userData['role'],
                createdAt: $userData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst uživatelská data z databáze. ' . $e->getMessage());
        }
    }

    /**
     * Get if user can manage other users
     * @return bool
     */
    public function isAdmin(): bool
    {
        return match ($this->role) {
            'admin', 'owner' => true,
            default => false,
        };
    }

    /**
     * Get if user can edit articles
     * @return bool
     */
    public function isEditor(): bool
    {
        return match ($this->role) {
            'admin', 'editor', 'owner' => true,
            default => false,
        };
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

    public function getFullname(): string
    {
        return $this->fullname;
    }


    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
