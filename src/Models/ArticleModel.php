<?php

namespace Models;

use Helpers\ReplaceHelper;
use Logic\DatabaseException;

class ArticleModel
{
    /**
     * @param string $title
     * @param string $subtitle
     * @param string $content
     * @param array<string> $imagePaths
     * @param int $authorId
     * @return void
     * @throws DatabaseException
     */
    public static function insertArticle(string $title, string $subtitle, string $content, array $imagePaths = [], int $authorId = 1): void
    {
        $slug = ReplaceHelper::getUrlFriendlyString($title);
        $imagePaths = implode(',', $imagePaths);

        DatabaseConnector::insert(
            table: 'article',
            items: ['title', 'subtitle', 'content', 'slug', 'image_path', 'author_id', 'created_at'],
            values: [$title, $subtitle, $content, $slug, $imagePaths, $authorId, date('Y-m-d')],
        );
    }

    /**
     * @param string $conditions
     * @return array<string>|null
     * @throws DatabaseException
     */
    public static function selectArticle(string $conditions): ?array
    {
        return DatabaseConnector::select(
            table: 'article',
            items: ['*'],
            conditions: $conditions,
        )[0];
    }

    /**
     * @param string|null $conditions
     * @return array<array<string>>|null
     * @throws DatabaseException
     */
    public static function selectArticles(?string $conditions = null): ?array
    {
        return DatabaseConnector::select(
            table: 'article',
            items: ['*'],
            conditions: $conditions,
        );
    }

    /**
     * @param int $id
     * @param string|null $title
     * @param string|null $subtitle
     * @param string|null $content
     * @param array<string> $imagePaths
     * @return void
     * @throws DatabaseException
     */
    public static function updateArticle(int $id, ?string $title = null, ?string $subtitle = null, ?string $content = null, ?array $imagePaths = []): void
    {
        $items = [];
        $values = [];

        switch (true) {
            case $title:
                $items[] = 'title';
                $values[] = $title;
                $items[] = 'slug';
                $values[] = ReplaceHelper::getUrlFriendlyString($title);
                // no break
            case $subtitle:
                $items[] = 'subtitle';
                $values[] = $subtitle;
                // no break
            case $content:
                $items[] = 'content';
                $values[] = $content;
                // no break
            case $imagePaths:
                $items[] = 'image_paths';
                $values[] = implode(',', $imagePaths);
                break;
        }

        DatabaseConnector::update(
            table: 'article',
            items: $items,
            values: $values,
            conditions: "WHERE id = $id",
        );
    }

    /**
     * @param int $id
     * @return void
     * @throws DatabaseException
     */
    public static function removeArticle(int $id): void
    {
        DatabaseConnector::remove(
            table: 'article',
            conditions: 'WHERE id = ' . $id,
        );
    }

    /**
     * @param int $id
     * @param string $string
     * @return void
     * @throws DatabaseException
     */
    public static function removeImageFromArticle(int $id, string $string): void
    {
        $imagePathsData = self::selectArticle(conditions: 'WHERE id = ' . $id);
        self::updateArticle(id: $id, imagePaths: array_diff($imagePathsData, [$string]));
    }
}
