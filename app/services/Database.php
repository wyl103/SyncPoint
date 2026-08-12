<?php
// app/services/Database.php

class Database {
    private $pdo;

    public function getConnection() {
        if ($this->pdo === null) {
            // Leer el .env (función nativa simple)
            $env = parse_ini_file(__DIR__ . '/../../.env');
            
            $host = $env['DB_HOST'];
            $db   = $env['DB_NAME'];
            $user = $env['DB_USER'];
            $pass = $env['DB_PASS'];
            $charset = 'utf8mb4';

            $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return $this->pdo;
    }
}