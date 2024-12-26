<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\UserModel;

/**
 * User
 *
 * The `User` class represents a user entity in the application. It provides functionality to fetch user
 * records from the database as well as methods to get and set user-related information, such as their
 * name, role, and image. This class also includes permissions-related utilities to determine
 * the user's capabilities based on their role.
 *
 * @package Logic
 */
class User
{
    /**
     * @var int $id The user's unique identifier
     */
    private int $id;

    /**
     * @var string $username The user's username
     */
    private string $username;

    /**
     * @var string $fullname The user's full name
     */
    private string $fullname;

    /**
     * @var string $image The path to the user's profile image
     */
    private string $image;

    /**
     * @var string $role The user's role (e.g., admin, owner, editor, etc.)
     */
    private string $role;

    /**
     * @var string $createdAt The date and time the user was created
     */
    private string $createdAt;


    /**
     * User constructor.
     *
     * @param int $id The user's unique identifier.
     * @param string $username The user's username.
     * @param string $fullname The user's full name.
     * @param string $image The path to the user's profile image.
     * @param string $role The user's role (e.g., admin, owner, editor, etc.).
     * @param string $createdAt The date and time the user was created.
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
     * Fetch a `User` object from the database using the username.
     *
     * @param string|null $username The username to look up in the database.
     *
     * @return User|null The user object with the fetched data.
     *
     * @throws Exception If the user cannot be retrieved.
     */
    public static function getUserByUsername(?string $username): ?User
    {
        if ($username === null) {
            return null;
        }

        try {
            $userData = UserModel::selectUser(username: $username);

            if (!$userData) {
                return null;
            } else {
                return self::returnUserObject($userData);
            }
        } catch (Exception $e) {
            throw new Exception('Error while fetching user from database with username');
        }
    }

    /**
     * Fetch a `User` object from the database using the user's ID.
     *
     * @param int|null $id The user's ID to look up in the database.
     *
     * @return User|null The user object with the fetched data.
     *
     * @throws Exception If the user cannot be retrieved.
     */
    public static function getUserById(?int $id): ?User
    {
        if ($id === null) {
            return null;
        }

        try {
            $userData = UserModel::selectUser(id: $id);

            if (!$userData) {
                return null;
            } else {
                return self::returnUserObject($userData);
            }
        } catch (Exception $e) {
            throw new Exception('Error while fetching user from database with id');
        }
    }

    /**
     * Return a User object from an array
     *
     * @param array<string, float|int|string|null> $userData
     * @return User
     * @throws Exception
     */
    private static function returnUserObject(array $userData): User
    {
        try {
            return new User(
                id: (int)$userData['id'],
                username: (string)$userData['username'],
                fullname: (string)$userData['fullname'],
                image: (string)file_exists((string)$userData['profile_image_path']) ? (string)$userData['profile_image_path'] : DEFAULT_PFP,
                role: (string)$userData['role'],
                createdAt: (string)$userData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Error while creating User object');
        }
    }

    /**
     * Check if the user has an admin or owner role.
     *
     * @return bool Returns `true` if the user is an admin or owner; otherwise `false`.
     */
    public function isAdmin(): bool
    {
        return match ($this->role) {
            'admin', 'owner' => true,
            default => false,
        };
    }

    /**
     * Check if the user has permission to edit articles.
     *
     * This method checks if the user role is `admin`, `editor`, or `owner`.
     *
     * @return bool Returns `true` if the user can edit articles; otherwise `false`.
     */
    public function isEditor(): bool
    {
        return match ($this->role) {
            'admin', 'editor', 'owner' => true,
            default => false,
        };
    }

    /**
     * @return int The user's ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string The username of the user
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string The full name of the user
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * @param string $fullname The new full name of the user
     */
    public function setFullname(string $fullname): void
    {
        $this->fullname = $fullname;
    }

    /**
     * @return string The path to the user's profile image
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Update the path of the user's profile image.
     *
     * @param string $imagePath The new path to the user's profile image.
     *
     * @return void
     */
    public function setImage(string $imagePath): void
    {
        $this->image = $imagePath;
    }

    /**
     * @return string The role of the user
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string The creation date in string format
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
