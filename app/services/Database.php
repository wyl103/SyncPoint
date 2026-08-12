<?php
// app/services/Database.php

class Database
{
    private $pdo;

    public function getConnection()
    {
        if ($this->pdo === null) {
            // Leer el .env
            $env = parse_ini_file(__DIR__ . '/../../.env');

            $host = $env['DB_HOST'];
            $port = $env['DB_PORT'] ?? '5432';
            $db = $env['DB_NAME'];
            $user = $env['DB_USER'];
            $pass = $env['DB_PASS'];
            $schema = $env['DB_SCHEMA'] ?? 'public'; // Esquema por defecto si no existe en .env

            // Se incluye la opción -c search_path dentro del DSN
            $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--search_path=$schema'";

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