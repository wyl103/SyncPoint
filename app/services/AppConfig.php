<?php
// app/services/AppConfig.php

class AppConfig {
    private static $configFile = __DIR__ . '/../config/config.json';

    public static function get($key, $default = null) {
        $config = self::load();
        return $config[$key] ?? $default;
    }

    public static function set($key, $value) {
        $config = self::load();
        $config[$key] = $value;
        return self::save($config);
    }

    public static function getAll() {
        return self::load();
    }

    private static function load() {
        if (!file_exists(self::$configFile)) {
            return [];
        }
        $content = file_get_contents(self::$configFile);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    private static function save(array $config) {
        $dir = dirname(self::$configFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return @file_put_contents(self::$configFile, $json) !== false;
    }
}
