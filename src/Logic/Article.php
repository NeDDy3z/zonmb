<?php

declare(strict_types=1);

namespace Logic;

class Article
{
    private int $id;
    private string $title;
    private string $subtitle;
    private string $content;
    private string $uri;
    private array $imagePath;
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
        $this->imagePath = $imagePaths;
        $this->authorId = $authorId;
        $this->createdAt = $createdAt;
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
        return $this->imagePath;
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
