<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\DatabaseConnector;

class User
{
    private string $username;
    private string $image;
    private string $role;
    private string $createdAt;


    /**
     * @param string $username
     * @throws DatabaseException
     * @throws Exception
     */
    public function __construct(string $username)
    {
        $this->username = $username;

        $userData = DatabaseConnector::selectUser(username: $username);

        try {
            $this->setUsername($userData['username']);
            $this->setRole($userData['role']);
            $this->setCreatedAt($userData['created_at']);
            $this->setImage( // Set default image on an empty img path
                file_exists($userData['profile_image_path']) ? $userData['profile_image_path'] : 'assets/uploads/profile_images/_default.png'
            );

        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst uživatelská data z databáze. ' . $e->getMessage());
        }
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

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
