<?php
// app/services/Database.php

class Database
{
    private $pdo;

    public function getConnection()
    {
        if ($this->pdo === null) {
            $envFile = __DIR__ . '/../../.env';
            $fileEnv = (file_exists($envFile) && is_readable($envFile)) ? @parse_ini_file($envFile) : [];
            if (!is_array($fileEnv)) {
                $fileEnv = [];
            }

            $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? ($fileEnv['DB_HOST'] ?? 'localhost')));
            $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? ($fileEnv['DB_PORT'] ?? '5432')));
            $db   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? ($fileEnv['DB_NAME'] ?? 'crionyx')));
            $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? ($fileEnv['DB_USER'] ?? 'postgres')));
            $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ($_SERVER['DB_PASS'] ?? ($fileEnv['DB_PASS'] ?? '')));
            $schema = getenv('DB_SCHEMA') ?: ($_ENV['DB_SCHEMA'] ?? ($_SERVER['DB_SCHEMA'] ?? ($fileEnv['DB_SCHEMA'] ?? 'public')));

            $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--search_path=\"$schema\",$schema,public'";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
        return $this->pdo;
    }
}