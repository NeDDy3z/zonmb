<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\DatabaseConnector;

class Article
{
    private int $id;
    private string $title;
    private string $subtitle;
    private string $content;
    private string $uri;
    private array $imagePaths;
    private int $authorId;
    private string $createdAt;

    /**
     * @param int $id
     * @param string $title
     * @param string $subtitle
     * @param string $content
     * @param string $uri
     * @param array<string> $imagePaths
     * @param int $authorId
     * @param string $createdAt
     */
    public function __construct(int $id, string $title, string $subtitle, string $content, string $uri, array $imagePaths, int $authorId, string $createdAt)
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->content = $content;
        $this->uri = $uri;
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
        $articleData = DatabaseConnector::selectArticle(slug: $slug);

        try {
            return new Article(
                id: (int)$articleData['id'],
                title: $articleData['title'],
                subtitle: $articleData['subtitle'],
                content: $articleData['content'],
                uri: $articleData['uri'],
                imagePaths: explode(', ', $articleData['image_paths']),
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
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return array<string>
     */
    public function getImagePaths(): array
    {
        /** @var array<string> $this */
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
