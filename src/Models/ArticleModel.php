<?php

namespace Models;

use Exception;
use Helpers\ReplaceHelper;
use Logic\DatabaseException;

/**
 * ArticleModel
 *
 * A database model class for managing articles. Provides methods for inserting, selecting, updating,
 * and removing articles from the database. Uses database connectors for data operations
 * and handles slugs and image paths internally.
 *
 * @package Models
 */
class ArticleModel
{
    /**
     * Insert a new article into the database.
     *
     * Creates a slug from the article title and stores image paths as a comma-separated string.
     *
     * @param string $title The title of the article.
     * @param string $subtitle The subtitle of the article.
     * @param string $content The main content of the article.
     * @param array<string>|null $imagePaths An array of image paths (optional).
     * @param int $authorId The ID of the author (default is 1).
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database insertion.
     */
    public static function insertArticle(string $title, string $subtitle, string $content, ?array $imagePaths = [], int $authorId = 1): void
    {
        // Create slug
        $slug = ReplaceHelper::getUrlFriendlyString($title);
        // Convert array of image paths into string where each path is split by ","
        $imagePaths = isset($imagePaths) ? implode(',', $imagePaths) : '';

        // Insert data
        DatabaseConnector::insert(
            table: 'article',
            items: ['title', 'subtitle', 'content', 'slug', 'image_paths', 'author_id', 'created_at'],
            values: [$title, $subtitle, $content, $slug, $imagePaths, $authorId, date('Y-m-d')],
        );
    }

    /**
     * Retrieve a single article from the database based on conditions.
     *
     * @param string $conditions The SQL conditions to filter the query.
     *
     * @return array<string>|null The article data as an associative array or `null` if not found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectArticle(string $conditions): ?array
    {
        // Select data
        return DatabaseConnector::select(
            table: 'article',
            items: ['*'],
            conditions: $conditions,
        )[0];
    }

    /**
     * Retrieve all articles from the database, with optional filtering conditions.
     *
     * @param string|null $conditions The SQL conditions to filter the query (optional).
     *
     * @return array<array<string>>|null An array of articles as associative arrays or `null` if none are found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectArticles(?string $conditions = null): ?array
    {
        // Select data
        return DatabaseConnector::select(
            table: 'article',
            items: ['*'],
            conditions: $conditions,
        );
    }

    /**
     * Check if an article title exists in the database.
     *
     * Returns `true` if the article is found or `false` otherwise.
     *
     * @param string $username The username to check for existence.
     *
     * @return bool The result of the existence check.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function existsArticle(string $username): bool
    {
        $exists = DatabaseConnector::select(
            table: 'article',
            items: ['title'],
            conditions: 'WHERE title = "' . $username . '"',
        );

        return (count($exists) > 0);
    }

    /**
     * Update an existing article in the database.
     *
     * Updates fields such as title, subtitle, content, and image paths based on what is provided.
     * Automatically generates a new slug if the title is updated.
     *
     * @param int $id The ID of the article to update.
     * @param string|null $title The new title (optional).
     * @param string|null $subtitle The new subtitle (optional).
     * @param string|null $content The new content (optional).
     * @param array<string>|null $imagePaths An array of new image paths (optional).
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database update.
     * @throws Exception If there is nothing to update.
     */
    public static function updateArticle(int $id, ?string $title = null, ?string $subtitle = null, ?string $content = null, ?array $imagePaths = []): void
    {
        // For each item, check if it is set, than add it to an arrays for change
        if ($title) {
            $items[] = 'title';
            $values[] = $title;

            $items[] = 'slug';
            $values[] = ReplaceHelper::getUrlFriendlyString($title);
        }
        if ($subtitle) {
            $items[] = 'subtitle';
            $values[] = $subtitle;
        }
        if ($content) {
            $items[] = 'content';
            $values[] = $content;
        }
        if ($imagePaths) {
            $items[] = 'image_paths';
            $values[] = implode(',', $imagePaths);
        }

        // Change data
        if (isset($items) and isset($values)) {
            DatabaseConnector::update(
                table: 'article',
                items: $items,
                values: $values,
                conditions: "WHERE id = $id",
            );
        } else {
            throw new Exception('Nothing to update');
        }
    }

    /**
     * Remove an article from the database.
     *
     * Deletes the article for the given ID.
     *
     * @param int $id The ID of the article to delete.
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the deletion.
     */
    public static function removeArticle(int $id): void
    {
        DatabaseConnector::remove(
            table: 'article',
            conditions: 'WHERE id = ' . $id,
        );
    }
}
