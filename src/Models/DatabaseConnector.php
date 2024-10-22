<?php
declare(strict_types=1);

namespace Zonmb\Models;

use Zonmb\Logic\DatabaseException;
use mysqli;
use mysqli_sql_exception;

class DatabaseConnector
{
    private static string $server;
    private static string $dbname;
    private static string $username;
    private static string $password;
    private static mysqli $connection;

    /**
     * @return void
     */
    public static function init(): void {
        $database = $_ENV['database'];
        self::$server = $database['server'];
        self::$dbname = $database['dbname'];
        self::$username = $database['username'];
        self::$password = $database['password'];
    }

    /**
     * @return void
     * @throws DatabaseException
     */
    private static function connect(): void {
        self::$connection = new mysqli(self::$server, self::$username, self::$password, self::$dbname);

        if (self::$connection->connect_error) {
            throw new DatabaseException('Nepodařilo se připojit k databázi: '. self::$connection->error);
        }
    }

    /**
     * @return void
     */
    private static function close(): void {
        self::$connection->close();
    }

    public static function isOpenThenClose(): void {
        if(self::$connection->ping()) {
            self::close();
        }
    }

    /**
     * @param string $table
     * @param array<string> $items
     * @param string|null $conditions
     * @return array<int<0, max>|array<string, float|int|string|null>>
     * @throws DatabaseException
     */
    private static function select(string $table, array $items, ?string $conditions): array {
        self::connect();

        $items = implode(separator: ',', array: $items);

        $query = "SELECT {$items} FROM {$table}";
        $query .= ($conditions) ? ' '.$conditions : null;

        try {
            $result = self::$connection->query($query);
        } catch (mysqli_sql_exception $e) {
            throw new DatabaseException('Nepodařilo se načíst data z databáze: '. $e->getMessage());
        }

        self::close();

        $resultArray = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($resultArray, $row);
            }
        }

        return $resultArray;
    }

    /**
     * @param string $table
     * @param array<string> $items
     * @param array<int, string|null> $values
     * @return void
     * @throws DatabaseException
     */
    private static function insert(string $table, array $items, array $values): void {
        self::connect();

        $items = implode(separator: ',', array: $items);
        $values = implode(separator: "','", array: $values);

        /** @lang MySQL */
        $query = "INSERT INTO {$table} ({$items})
                  VALUES ('{$values}');";


        if (self::$connection->query($query) !== True) {
            throw new DatabaseException('Nepodařilo se vložit data do databáze: '. self::$connection->error);
        }

        self::close();
    }



    // User manipulation
    /**
     * @param string $username
     * @return array<string, float|int|string|null>
     * @throws DatabaseException
     */
    public static function selectUser(string $username): array {
        return self::select(
            table: 'user',
            items: ['*'],
            conditions: 'WHERE username = "'. $username .'"',
        )[0];
    }

    /**
     * @return array<array<string, float|int|string|null>|int<0, max>>
     * @throws DatabaseException
     */
    public static function selectUsers(): array {
        return self::select(
            table: 'user',
            items: ['username', 'role', 'created_at'],
            conditions: null,
        );
    }

    /**
     * @param string $username
     * @return array<array<string, float|int|string|null>|int<0, max>>
     * @throws DatabaseException
     */
    public static function existsUser(string $username): array {
        return self::select(
            table: 'user',
            items: ['username'],
            conditions: 'WHERE username LIKE "'. $username .'"',
        );
    }


    /**
     * @param string $username
     * @param string $password
     * @param string|null $profile_image_path
     * @return void
     * @throws DatabaseException
     */
    public static function insertUser(string $username, string $password, ?string $profile_image_path): void {
        $items = ['username', 'password', 'role'];
        $values = [$username, $password, 'user'];

        if ($profile_image_path) {
            array_push($items, 'profile_image_path');
            array_push($values, 'assets/uploads/profile_images/'. $profile_image_path);
        }

        self::insert(
            table: 'user',
            items: $items,
            values: $values,
        );
    }


    // Article manipulation
}
