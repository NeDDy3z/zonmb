<?php

namespace Models;

use Controllers\Controller;
use Logic\DatabaseException;

/**
 * CommentModel
 *
 * A database model class for managing comments. Provides methods for inserting, selecting,
 * and removing comments from the database. Uses database connectors for data operations.
 *
 * @package Models
 * @author Erik Vaněk
 */
class CommentModel extends Controller
{
    /**
     * Insert a new comment into the database.
     *
     * @param string $text
     * @param int $articleId
     * @param int $authorId The ID of the author (default is 1).
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the database insertion.
     */
    public static function insertComment(string $text, int $articleId, int $authorId): void
    {
        // Insert data
        DatabaseConnector::insert(
            table: 'comment',
            items: ['text', 'article_id', 'author_id', 'created_at'],
            values: [$text, $articleId, $authorId, date('Y-m-d')],
        );
    }

    /**
     * Retrieve a single comment from the database based on conditions.
     *
     * @param string $conditions The SQL conditions to filter the query.
     *
     * @return array<string>|null The comment data as an associative array or `null` if not found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectComment(string $conditions): ?array
    {
        // Select data
        return DatabaseConnector::select(
            table: 'comment',
            items: ['*'],
            conditions: $conditions,
        )[0] ?? null;
    }

    /**
     * Retrieve all comments from the database, with optional filtering conditions.
     *
     * @param string|null $conditions The SQL conditions to filter the query (optional).
     *
     * @return array<array<string>>|null An array of articles as associative arrays or `null` if none are found.
     *
     * @throws DatabaseException If there is an issue with the database query.
     */
    public static function selectComments(?string $conditions = null): ?array
    {
        // Select data
        return DatabaseConnector::select(
            table: 'comment',
            items: ['comment.id as id', 'comment.article_id as article_id', 'comment.author_id as author_id', 'user.fullname as author', 'comment.text as text', 'comment.created_at as created_at'],
            conditions: 'JOIN user on comment.author_id = user.id '. $conditions,
        );
    }

    /**
     * Remove a comment from the database
     *
     * Deletes the article for the given ID.
     *
     * @param int $id The ID of the article to delete.
     *
     * @return void
     *
     * @throws DatabaseException If there is an issue with the deletion.
     */
    public static function removeComment(int $id): void
    {
        DatabaseConnector::remove(
            table: 'comment',
            conditions: 'WHERE id = ' . $id,
        );
    }
}