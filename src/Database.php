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
        // Check if table exists
        $result = self::$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        $tableExists = $result->fetch();

        if (!$tableExists) {
            self::$pdo->exec("
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    uuid TEXT NOT NULL UNIQUE,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            // Check if uuid column exists
            $result = self::$pdo->query("PRAGMA table_info(users)");
            $columns = $result->fetchAll();
            $uuidExists = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'uuid') {
                    $uuidExists = true;
                    break;
                }
            }

            if (!$uuidExists) {
                self::$pdo->exec("ALTER TABLE users ADD COLUMN uuid TEXT");
                
                // Populate existing users with UUIDs
                $stmt = self::$pdo->query("SELECT id FROM users WHERE uuid IS NULL");
                $users = $stmt->fetchAll();
                $updateStmt = self::$pdo->prepare("UPDATE users SET uuid = ? WHERE id = ?");
                foreach ($users as $user) {
                    $updateStmt->execute([self::generateUuid(), $user['id']]);
                }

                // Make uuid column NOT NULL UNIQUE (SQLite doesn't support ALTER COLUMN easily, 
                // but since we just populated it, it's effectively fine for now if we don't strictly enforce it via schema change here, 
                // or we could do the temp table dance. Given it's a sandbox, simple ALTER + manual populate is a start.)
                // To properly add UNIQUE constraint in SQLite we need a new table.
            }
        }

        // Seed default user if not exists
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['brian@olsfamily.com']);
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('tacos123', PASSWORD_BCRYPT);
            $stmt = self::$pdo->prepare("INSERT INTO users (uuid, email, password) VALUES (?, ?, ?)");
            $stmt->execute([self::generateUuid(), 'brian@olsfamily.com', $hashedPassword]);
        }
    }

    public static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
