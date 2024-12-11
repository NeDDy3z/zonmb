<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\ArticleModel;
use Models\DatabaseConnector;

class Article
{
    private int $id;
    private string $title;
    private string $subtitle;
    private string $content;
    private string $slug;
    private ?array $imagePaths;
    private int $authorId;
    private string $createdAt;

    /**
     * @param int $id
     * @param string $title
     * @param string $subtitle
     * @param string $content
     * @param string $slug
     * @param array<string>|null $imagePaths
     * @param int $authorId
     * @param string $createdAt
     */
    public function __construct(int $id, string $title, string $subtitle, string $content, string $slug, ?array $imagePaths, int $authorId, string $createdAt)
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->content = $content;
        $this->slug = $slug;
        $this->imagePaths = $imagePaths;
        $this->authorId = $authorId;
        $this->createdAt = $createdAt;
    }


    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public static function getArticleBySlug(string $slug): Article
    {
        $articleData = ArticleModel::selectArticle('WHERE slug = "'. $slug .'" LIMIT 1;');

        if (!$articleData) {
            throw new Exception('Nepodařilo se načíst článek z databáze podle nazvu.');
        }

        $imagePaths = isset($articleData['image_paths']) ? explode(',', $articleData['image_paths']) : null;

        try {
            return new Article(
                id: (int)$articleData['id'],
                title: $articleData['title'],
                subtitle: $articleData['subtitle'],
                content: $articleData['content'],
                slug: $articleData['slug'],
                imagePaths: $imagePaths,
                authorId: (int)$articleData['author_id'],
                createdAt: $articleData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst článek z databáze. ' . $e->getMessage());
        }
    }

    /**
     * @param string|null $id
     * @return Article|null
     * @throws DatabaseException
     */
    public static function getArticleById(?string $id): ?Article
    {
        if (!$id) {
            return null;
        }

        $articleData = ArticleModel::selectArticle('WHERE id = '. $id .' LIMIT 1;');

        if (!$articleData) {
            throw new Exception('Nepodařilo se načíst článek z databáze podle id.');
        }

        $imagePaths = isset($articleData['image_paths']) ? explode(',', $articleData['image_paths']) : null;


        try {
            return new Article(
                id: (int)$articleData['id'],
                title: $articleData['title'],
                subtitle: $articleData['subtitle'],
                content: $articleData['content'],
                slug: $articleData['slug'],
                imagePaths: $imagePaths,
                authorId: (int)$articleData['author_id'],
                createdAt: $articleData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Nepodařilo se načíst článek z databáze. ' . $e->getMessage());
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return array<string>|null
     */
    public function getImagePaths(): ?array
    {
        return $this->imagePaths;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
