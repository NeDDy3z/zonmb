<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;
use Logic\DatabaseException;

class DatabaseConnector
{
    private static string $server;
    private static string $dbname;
    private static string $username;
    private static string $password;
    private static ?PDO $connection;


    /**
     * Load database credentials from config file
     * @return void
     * @throws DatabaseException
     */
    public static function init(): void
    {
        if (!isset($_ENV['database'])) {
            throw new DatabaseException('Nepodařilo se načíst konfigurační soubor databáze');
        }

        $database = $_ENV['database'];
        self::$server = $database['server'];
        self::$dbname = $database['dbname'];
        self::$username = $database['username'];
        self::$password = $database['password'];
    }

    /**
     * Connect to the database
     * @return void
     * @throws DatabaseException
     */
    private static function connect(): void
    {
        try {
            self::$connection = new PDO(
                dsn: "mysql:host=" . self::$server . ";dbname=" . self::$dbname,
                username: self::$username,
                password: self::$password,
                options: [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            );
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se připojit k databázi, některé funkce webu budou omezeny. Chyba: ' . $e->getMessage());
        }
    }


    /**
     * Close connection
     * @return void
     */
    public static function close(): void
    {
        self::$connection = null;
    }

    /**
     * Template function for selecting data from database
     * @param string $table
     * @param array<string> $items
     * @param string|null $conditions
     * @return array<array<string>>
     * @throws DatabaseException
     */
    private static function select(string $table, array $items, ?string $conditions): array
    {
        // If connection is null create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // Prepare items for query
        $items = implode(separator: ',', array: $items);

        // Create query
        $query = "SELECT {$items} FROM {$table}";
        $query .= ($conditions) ? ' ' . $conditions : null;

        // Execute query and fetch data
        try {
            $result = self::$connection->query($query)->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se načíst data z databáze: ' . $e->getMessage());
        }

        // Convert into Array<Array<String>>
        $resultArray = [];
        foreach ($result as $row) {
            $resultArray[] = array_filter(
                $row,
                function ($key) {
                    return !is_int($key);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $resultArray;
    }

    /**
     * Template function for inserting data into database
     * @param string $table
     * @param array<string> $items
     * @param array<int, string|null> $values
     * @return void
     * @throws DatabaseException
     */
    private static function insert(string $table, array $items, array $values): void
    {
        // If connection is null create new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // Prepare items for query
        $itemRows = implode(separator: ',', array: $items);
        $itemVals = implode(',', array_fill(0, count($items), '?'));

        // Create query
        $query = "INSERT INTO {$table} ({$itemRows}) VALUES ({$itemVals});";

        // Execute query with values. Check if data were inserted
        try {
            self::$connection->prepare($query)->execute($values);
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se vložit data do databáze: ' . $e->getMessage());
        }
    }



    // User manipulation

    /**
     * Get user data from database
     * @param string $username
     * @return array<string, float|int|string|null>|null
     * @throws DatabaseException
     */
    public static function selectUser(string $username): ?array
    {
        return self::select(
            table: 'user',
            items: ['*'],
            conditions: 'WHERE username = "' . $username . '"',
        )[0];
    }

    /**
     * Get all users from database
     * @return array<array<string, float|int|string|null>|int<0, max>>|null
     * @throws DatabaseException
     */
    public static function selectUsers(): ?array
    {
        return self::select(
            table: 'user',
            items: ['username', 'role', 'created_at'],
            conditions: null,
        );
    }

    /**
     * Check if user exists in database
     * @param string $username
     * @return array<array<string, float|int|string|null>|int<0, max>>|null
     * @throws DatabaseException
     */
    public static function existsUser(string $username): ?array
    {
        return self::select(
            table: 'user',
            items: ['username'],
            conditions: 'WHERE username LIKE "' . $username . '"',
        );
    }


    /**
     * @param string $username
     * @param string $password
     * @param string|null $profile_image_path
     * @return void
     * @throws DatabaseException
     */
    public static function insertUser(string $username, string $password, ?string $profile_image_path): void
    {
        $items = ['username', 'password', 'role'];
        $values = [$username, $password, 'user'];

        if ($profile_image_path) {
            array_push($items, 'profile_image_path');
            array_push($values, $profile_image_path);
        }

        self::insert(
            table: 'user',
            items: $items,
            values: $values,
        );
    }


    // Article manipulation

    /**
     * @param string $title
     * @param string $subtitle
     * @param string $content
     * @param string $uri
     * @param array $imagePaths
     * @param int $authorId
     * @return void
     * @throws DatabaseException
     */
    public static function insertArticle(string $title, string $subtitle, string $content, string $uri = '', array $imagePaths = [], int $authorId = 1): void
    {
        $uri ?? 'news/'.urlencode($title);
        $imagePaths = implode(',', $imagePaths);

        self::insert(
            table: 'article',
            items: ['title', 'subtitle', 'content', 'uri', 'image_path', 'author_id', 'created_at'],
            values: [$title, $subtitle, $content, $uri, $imagePaths, $authorId, date('Y-m-d')],
        );
    }

    /**
     * @return array<array<string>>
     * @throws DatabaseException
     */
    public static function selectArticles(): array
    {
        return self::select(
            table: 'article',
            items: ['*'],
            conditions: null,
        );
    }
}
