<?php

declare(strict_types=1);

namespace Models;

use Helpers\ReplaceHelper;
use PDO;
use PDOException;
use Logic\DatabaseException;

class DatabaseConnector // TODO: Refactor this class to use Dependency Injection
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
    public static function select(string $table, array $items, ?string $conditions): array
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
     * @param array<int, int|string> $values
     * @return void
     * @throws DatabaseException
     */
    public static function insert(string $table, array $items, array $values): void
    {
        // If connection is null create new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        if (count($items) !== count($values)) {
            throw new DatabaseException('Počet položek a hodnot neodpovídá');
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

    /**
     * Template function for updating data in database
     * @param string $table
     * @param array<string> $items
     * @param array<int, string|null> $values
     * @param string $conditions
     * @return void
     * @throws DatabaseException
     */
    public static function update(string $table, array $items, array $values, string $conditions): void
    {
        // If connection is null create new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        if (count($items) !== count($values)) {
            throw new DatabaseException('Počet položek a hodnot pro aktualizaci neodpovídá');
        }

        // Prepare items for query
        foreach ($items as $key => $item) {
            $items[$key] = $item . ' = ?';
        }

        // Prepare values for query
        foreach ($values as $key => $value) {
            if ($value === 'null') {
                $values[$key] = null;
            }
        }

        $itemRows = implode(separator: ' , ', array: $items);

        // Create query
        $query = "UPDATE {$table} SET {$itemRows} {$conditions};";

        // Execute query with values. Check if data were inserted
        try {
            self::$connection->prepare($query)->execute($values);
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se upravit data v databázi: ' . $e->getMessage());
        }
    }

    /**
     * Template function for deleting data from database
     * @param string $table
     * @param string $conditions
     * @return void
     * @throws DatabaseException
     */
    public static function remove(string $table, string $conditions): void
    {
        // If connection is null create new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // Create query
        $query = "DELETE FROM {$table} {$conditions};";

        // Execute query. Check if data were inserted
        try {
            self::$connection->prepare($query)->execute();
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se odstranit data z databáze: ' . $e->getMessage());
        }
    }

    /**
     * Count number of columns - good for paging
     * @param string $table
     * @return int
     * @throws DatabaseException
     */
    public static function count(string $table): int
    {
        // If connection is null create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "SELECT COUNT(id) as column_count FROM {$table};";

        // Execute query and fetch data
        try {
            $result = self::$connection->query($query)->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se načíst data z databáze: ' . $e->getMessage());
        }

        return (int)$result[0]['column_count'];
    }

    /**
     * Get top id - used for articles
     * @param string $table
     * @return int
     * @throws DatabaseException
     */
    public static function selectMaxId(string $table): int
    {
        // If connection is null create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "SELECT MAX(id) as max_id FROM {$table};";

        // Execute query and fetch data
        try {
            $result = self::$connection->query($query)->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se načíst data z databáze: ' . $e->getMessage());
        }

        return (int)$result[0]['max_id'];
    }

    /**
     * Reset auto increment on an empty db
     * @param string $table
     * @return void
     * @throws DatabaseException
     */
    public static function resetAutoIncrement(string $table): void
    {
        // If connection is null create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "ALTER TABLE {$table} AUTO_INCREMENT = 1;";

        // Execute query and fetch data
        try {
            self::$connection->prepare($query)->execute();
        } catch (PDOException $e) {
            throw new DatabaseException('Nepodařilo se načíst data z databáze: ' . $e->getMessage());
        }
    }
}
