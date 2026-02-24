<?php

namespace App;

use PDO;

class Database
{
    private static $pdo;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../database/database.sqlite';
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            self::$pdo = new PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            self::initialize();
        }

        return self::$pdo;
    }

    private static function initialize(): void
    {
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Seed default user if not exists
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['brian@olsfamily.com']);
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('tacos123', PASSWORD_BCRYPT);
            $stmt = self::$pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute(['brian@olsfamily.com', $hashedPassword]);
        }
    }
}
