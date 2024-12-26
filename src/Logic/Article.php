<?php

declare(strict_types=1);

namespace Logic;

use Exception;
use Models\ArticleModel;

/**
 * Article
 *
 * The `Article` class represents an article entity, including its title, subtitle, content, slug,
 * images, author details, and creation date. It includes methods for fetching articles
 * by their slug or ID directly from the database.
 *
 * @package Logic
 */
class Article
{
    /**
     * @var int $id The unique identifier of the article
     */
    private int $id;

    /**
     * @var string $title The title of the article
     */
    private string $title;

    /**
     * @var string $subtitle The subtitle of the article
     */
    private string $subtitle;

    /**
     * @var string $content The main content of the article
     */
    private string $content;

    /**
     * @var string $slug The unique slug (URL-friendly identifier) of the article
     */
    private string $slug;

    /**
     * @var string[]|null $imagePaths List of image paths associated with the article, or `null` if no images
     */
    private ?array $imagePaths;

    /**
     * @var int $authorId The ID of the user who authored the article
     */
    private int $authorId;

    /**
     * @var string $createdAt The date and time when the article was created
     */
    private string $createdAt;

    /**
     * Article constructor.
     *
     * Initializes the properties of the `Article` object, including handling an empty image path
     * array by converting it to `null`.
     *
     * @param int $id The unique identifier of the article.
     * @param string $title The title of the article.
     * @param string $subtitle The subtitle of the article.
     * @param string $content The main text content of the article.
     * @param string $slug The slug (URL identifier) for the article.
     * @param array<string>|null $imagePaths Paths to images linked to the article.
     * @param int $authorId The ID of the user who authored the article.
     * @param string $createdAt The date and time of the article's creation.
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

        if ($this->imagePaths and $this->imagePaths[0] === "") {
            $this->imagePaths = null;
        }
    }

    /**
     * Retrieve an article from the database based on its ID.
     *
     * Uses the `ArticleModel` to fetch the article details and construct an `Article` object.
     *
     * @param int|null $id The unique identifier of the article.
     *
     * @return Article|null The `Article` object representing the fetched article.
     *
     * @throws Exception If the article could not be found or another error occurs.
     */
    public static function getArticleById(?int $id): ?Article
    {
        if ($id === null) {
            return null;
        }

        try {
            // Select article from a database
            $articleData = ArticleModel::selectArticle('WHERE id = '. $id .' LIMIT 1;');

            if (!$articleData) {
                return null;
            } else {
                return self::returnArticleObject($articleData);
            }
        } catch (Exception $e) {
            throw new Exception('Error while fetching article from database with id');
        }
    }

    /**
     * Retrieve an article from the database based on its slug.
     *
     * Uses the `ArticleModel` to fetch the article details and constructs an `Article` object.
     *
     * @param string $slug The slug (URL-friendly identifier) of the article.
     *
     * @return Article The `Article` object representing the fetched article.
     *
     * @throws DatabaseException If a database-related error occurs.
     * @throws Exception If the article could not be found or another error occurs.
     */
    public static function getArticleBySlug(?string $slug): ?Article
    {
        if ($slug === null) {
            return null;
        }

        try {
            // Select article from a database
            $articleData = ArticleModel::selectArticle('WHERE slug = "'. $slug .'" LIMIT 1;');

            if (!$articleData) {
                return null;
            } else {
                return self::returnArticleObject($articleData);
            }
        } catch (Exception $e) {
            throw new Exception('Error while fetching article from database with slug');
        }
    }

    /**
     * Return an Article object from an array
     *
     * @param array<string, string> $articleData
     * @return Article
     * @throws Exception
     *
     * @package Logic
     */
    private static function returnArticleObject(array $articleData): Article
    {
        try {
            return new Article(
                id: (int)$articleData['id'],
                title: $articleData['title'],
                subtitle: $articleData['subtitle'],
                content: $articleData['content'],
                slug: $articleData['slug'],
                imagePaths: isset($articleData['image_paths']) ? explode(',', $articleData['image_paths']) : null,
                authorId: (int)$articleData['author_id'],
                createdAt: $articleData['created_at'],
            );
        } catch (Exception $e) {
            throw new Exception('Error while creating Article object');
        }
    }

    /**
     * @return int The article's ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string The article's title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string The article's subtitle
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @return string The article's text content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string The article's slug
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return array<string>|null A list of image paths or `null` if no images are associated
     */
    public function getImagePaths(): ?array
    {
        return $this->imagePaths;
    }

    /**
     * @return int The author's user ID
     */
    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    /**
     * @return string The article's creation date and time
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
