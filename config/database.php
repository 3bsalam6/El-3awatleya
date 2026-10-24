<?php
/**
 * العواطلية - Database Configuration
 * SQL Server connection using PDO with sqlsrv driver (Windows Auth)
 */

class Database
{
    private static $instance = null;
    private $connection;

    private $host = 'DESKTOP-OG4GIGD';
    private $database = 'El3awatleya';

    private function __construct()
    {
        try {
            $dsn = "sqlsrv:Server={$this->host};Database={$this->database}";
            $this->connection = new PDO($dsn, '', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function __clone() {}
}
