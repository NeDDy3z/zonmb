<?php

namespace Models;

use Exception;
use Helpers\ReplaceHelper;
use Logic\DatabaseException;

/**
 * UserModel
 *
 * A database model that provides various methods to interact with the `user` table in the database.
 * This class allows for selecting, inserting, and updating user records, as well as checking for
 * user existence. Throws `DatabaseException` in case of database errors.
 *
 * @package Models
 */
class UserModel
{
    /**
     * Retrieve a single user from the database.
     *
     * Supports querying based on either the user's ID or username.
     *
     * @param int|null $id The user's unique ID (optional).
     * @param string|null $username The username of the user (optional).
     *
     * @return array<string, float|int|string|null>|null The user's data as an associative array, or `null` if not found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectUser(?int $id = null, ?string $username = null): ?array
    {
        if (!$id and !$username) {
            return null;
        }

        $condition = ($id) ? "WHERE id = $id" : "WHERE username = '$username'";

        return DatabaseConnector::select(
            table: 'user',
            items: ['*'],
            conditions: $condition,
        )[0];
    }

    /**
     * Retrieve all users from the database.
     *
     * Optionally, conditions can be provided to filter the results.
     *
     * @param string|null $conditions Conditions to filter the query (optional).
     *
     * @return array<array<string, float|int|string|null>|int<0, max>>|null An array of users or `null` if no users found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectUsers(?string $conditions = null): ?array
    {

        return DatabaseConnector::select(
            table: 'user',
            items: ['*'],
            conditions: $conditions,
        );
    }

    /**
     * Check if a user exists in the database by their username.
     *
     * Returns `true` if the user is found or `false` otherwise.
     *
     * @param string $username The username to check for existence.
     *
     * @return bool The result of the existence check.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function existsUser(string $username): bool
    {

        $exists = DatabaseConnector::select(
            table: 'user',
            items: ['username'],
            conditions: 'WHERE username LIKE "' . $username . '"',
        );

        return (count($exists) > 0);
    }

    /**
     * Insert a new user into the database.
     *
     * By default, new users will have the role of `user`. Optionally, a profile image path may be included.
     *
     * @param string $username The username of the new user.
     * @param string $fullname The full name of the new user.
     * @param string $password The password for the new user.
     * @param string|null $profile_image_path The path to the user's profile image (optional).
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database insertion.
     */
    public static function insertUser(string $username, string $fullname, string $password, ?string $profile_image_path): void
    {
        $items = ['username', 'fullname', 'password', 'role'];
        $values = [$username, $fullname, $password, 'user'];

        if ($profile_image_path) {
            $items[] = 'profile_image_path';
            $values[] = $profile_image_path;
        }

        DatabaseConnector::insert(
            table: 'user',
            items: $items,
            values: $values,
        );
    }

    /**
     * Update an existing user in the database.
     *
     * Updates fields such as fullname, role and profile image path based on what is provided.
     *
     * @param int $id The ID of the article to update.
     * @param string|null $fullname
     * @param string|null $role
     * @param string|null $profile_image_path
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database update.
     * @throws Exception
     */
    public static function updateUser(int $id, ?string $fullname = null, ?string $role = null, ?string $profile_image_path = null): void
    {
        // For each item, check if it is set, than add it to an arrays for change
        if ($fullname) {
            $items[] = 'fullname';
            $values[] = $fullname;
        }
        if ($role) {
            $items[] = 'role';
            $values[] = $role;
        }
        if ($profile_image_path) {
            $items[] = 'profile_image_path';
            $values[] = $profile_image_path;
        }

        // Change data
        if (isset($items) and isset($values)) {
            DatabaseConnector::update(
                table: 'user',
                items: $items,
                values: $values,
                conditions: "WHERE id = $id",
            );
        } else {
            throw new Exception('Nothing to update');
        }
    }

    /**
     * Update a user's full name in the database.
     *
     * @param int $id The ID of the user to update.
     * @param string $fullname The new full name.
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database update.
     */
    public static function updateUserFullname(int $id, string $fullname): void
    {
        DatabaseConnector::update(
            table: 'user',
            items: ['fullname'],
            values: [$fullname],
            conditions: 'WHERE id = ' . $id,
        );
    }

    /**
     * Update a user's profile image path in the database.
     *
     * @param int $id The ID of the user to update.
     * @param string $profile_image_path The new profile image path.
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database update.
     */
    public static function updateUserProfileImage(int $id, string $profile_image_path): void
    {
        DatabaseConnector::update(
            table: 'user',
            items: ['profile_image_path'],
            values: [$profile_image_path],
            conditions: 'WHERE id = ' . $id,
        );
    }

    /**
     * Remove user from the database.
     *
     * Deletes the user for the given ID.
     *
     * @param int $id The ID of the user to delete.
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the deletion.
     */
    public static function removeUser(int $id): void
    {
        DatabaseConnector::remove(
            table: 'user',
            conditions: 'WHERE id = ' . $id,
        );
    }
}
