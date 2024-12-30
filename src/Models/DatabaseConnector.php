<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;
use Logic\DatabaseException;

/**
 * DatabaseConnector
 *
 * Provides a static interface for interacting with the database.
 * Includes methods for CRUD operations, connection management, and utility queries.
 *
 * @package Models
 * @author Erik Vaněk
 */
class DatabaseConnector
{
    /**
     * @var string $server The server address of the database.
     */
    private static string $server;

    /**
     * @var string $dbname The name of the database to connect to.
     */
    private static string $dbname;

    /**
     * @var string $username The username for database login.
     */
    private static string $username;

    /**
     * @var string $password The password for database login.
     */
    private static string $password;

    /**
     * @var PDO|null $connection The PDO database connection instance.
     */
    private static ?PDO $connection;

    /**
     * Initialize the database settings by loading credentials from environment variables.
     *
     * @return void
     * @throws DatabaseException If database credentials are missing in environment configuration.
     */
    public static function init(): void
    {
        // Check for database credentials
        if (!isset($_ENV['database'])) {
            throw new DatabaseException('Error while loading database credentials: Credentials are not set as environment variables');
        }

        // Load database credentials
        $database = $_ENV['database'];
        self::$server = $database['server'];
        self::$dbname = $database['dbname'];
        self::$username = $database['username'];
        self::$password = $database['password'];
    }

    /**
     * Establish a PDO connection to the database.
     *
     * @return void
     * @throws DatabaseException If the connection to the database fails.
     */
    private static function connect(): void
    {
        // Try to connect to the database
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
            throw new DatabaseException('Error while connecting to database, some functions will be limited');
        }
    }


    /**
     * Close the database connection.
     *
     * @return void
     */
    public static function close(): void
    {
        self::$connection = null;
    }

    /**
     * Perform a SELECT query on the database.
     *
     * @param string $table The table to perform the query on.
     * @param array<string> $items The columns to select, e.g., ['id', 'name'].
     * @param string|null $conditions The WHERE clause or other conditions (optional).
     *
     * @return array<array<string>> The result set as an array of associative arrays.
     * @throws DatabaseException If the query execution fails.
     */
    public static function select(string $table, array $items, ?string $conditions): array
    {
        // If connection is null try to create a new connection
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
            $result = self::$connection?->query($query)?->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Error while loading data from database'. $e->getMessage());
        }

        // Convert into Array<Array<String>>
        $resultArray = [];
        if ($result) {
            foreach ($result as $row) {
                $resultArray[] = array_filter(
                    $row,
                    function ($key) {
                        return !is_int($key);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }
        }

        return $resultArray;
    }

    /**
     * Insert a new record into a database table.
     *
     * @param string $table The table to insert the data into.
     * @param array<string> $items The columns to insert into.
     * @param array<int, int|string> $values The values to insert for the respective columns.
     *
     * @return void
     * @throws DatabaseException If the insertion fails or column-value count mismatch.
     */
    public static function insert(string $table, array $items, array $values): void
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // If number of items and values does not equal, throw an exception
        if (count($items) !== count($values)) {
            throw new DatabaseException('Number of items and values does not equal, data insertion cancelled');
        }

        // Prepare items for query
        $itemRows = implode(separator: ',', array: $items);
        $itemVals = implode(',', array_fill(0, count($items), '?'));

        // Create query
        $query = "INSERT INTO {$table} ({$itemRows}) VALUES ({$itemVals});";

        // Execute query with values. Check if data were inserted
        try {
            self::$connection?->prepare($query)->execute($values);
        } catch (PDOException $e) {
            throw new DatabaseException('Error while inserting data into database'. $e->getMessage());
        }
    }

    /**
     * Update existing records in a database table.
     *
     * @param string $table The table to update the data in.
     * @param array<string> $items The columns to update.
     * @param array<int, string|null> $values The new values for the respective columns.
     * @param string $conditions The WHERE clause to specify which rows to update.
     *
     * @return void
     * @throws DatabaseException If the update fails or column-value count mismatch.
     */
    public static function update(string $table, array $items, array $values, string $conditions): void
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // If number of items and values does not equal, throw an exception
        if (count($items) !== count($values)) {
            throw new DatabaseException('Number of items and values does not equal, data update cancelled');
        }

        // Prepare items for query
        foreach ($items as $key => $item) {
            $items[$key] = $item . ' = ?';
        }
        $itemRows = implode(separator: ' , ', array: $items);

        // Prepare values for query
        foreach ($values as $key => $value) {
            if ($value === 'null') {
                $values[$key] = null;
            }
        }

        // Create query
        $query = "UPDATE {$table} SET {$itemRows} {$conditions};";

        // Execute query with values. Check if data were inserted
        try {
            self::$connection?->prepare($query)->execute($values);
        } catch (PDOException $e) {
            throw new DatabaseException('Error while updating data in database');
        }
    }

    /**
     * Delete records from a database table based on conditions.
     *
     * @param string $table The table to delete data from.
     * @param string $conditions The WHERE clause to specify which rows to delete.
     *
     * @return void
     * @throws DatabaseException If the deletion fails.
     */
    public static function remove(string $table, string $conditions): void
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        // Create query
        $query = "DELETE FROM {$table} {$conditions};";

        // Execute query. Check if data were inserted
        try {
            self::$connection?->prepare($query)->execute();
        } catch (PDOException $e) {
            throw new DatabaseException('Error while removing data from database');
        }
    }

    /**
     * Count the number of rows in a database table.
     *
     * @param string $table The table to count rows from.
     *
     * @return int The number of rows in the specified table.
     * @throws DatabaseException If the query execution fails.
     */
    public static function count(string $table): int
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "SELECT COUNT(id) as column_count FROM {$table};";

        // Execute query and fetch data
        try {
            $result = self::$connection->query($query)->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Error while loading number of columns from database table');
        }

        return (int)$result[0]['column_count'];
    }

    /**
     * Retrieve the highest ID from a database table.
     *
     * @param string $table The table to retrieve the max ID from.
     *
     * @return int The highest ID in the table.
     * @throws DatabaseException If the query execution fails.
     */
    public static function selectMaxId(string $table): int
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "SELECT MAX(id) as max_id FROM {$table};";

        // Execute query and fetch data
        try {
            $result = self::$connection->query($query)->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException('Error while loading highest id from database table');
        }

        return (int)$result[0]['max_id'];
    }

    /**
     * Reset the AUTO_INCREMENT counter for a table.
     *
     * @param string $table The table to reset the AUTO_INCREMENT counter.
     *
     * @return void
     * @throws DatabaseException If the query execution fails.
     */
    public static function resetAutoIncrement(string $table): void
    {
        // If connection is null try to create a new connection
        if (!isset(self::$connection)) {
            self::connect();
        }

        $query = "ALTER TABLE {$table} AUTO_INCREMENT = 1;";

        // Execute query and fetch data
        try {
            self::$connection->prepare($query)->execute();
        } catch (PDOException $e) {
            throw new DatabaseException('Error while resetting auto increment on an empty table');
        }
    }
}
