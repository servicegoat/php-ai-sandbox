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
                    id TEXT PRIMARY KEY,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            // Check if schema needs update (id column type)
            $result = self::$pdo->query("PRAGMA table_info(users)");
            $columns = $result->fetchAll();
            $idType = '';
            $hasUuid = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'id') {
                    $idType = strtoupper($column['type']);
                }
                if ($column['name'] === 'uuid') {
                    $hasUuid = true;
                }
            }

            if ($idType === 'INTEGER') {
                // Migration: INTEGER id -> TEXT uuid-based id
                self::$pdo->beginTransaction();
                try {
                    // 1. Create new table
                    self::$pdo->exec("
                        CREATE TABLE users_new (
                            id TEXT PRIMARY KEY,
                            email TEXT NOT NULL UNIQUE,
                            password TEXT NOT NULL,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )
                    ");

                    // 2. Copy data
                    if ($hasUuid) {
                        // If we have uuid column, use it as new id
                        self::$pdo->exec("INSERT INTO users_new (id, email, password, created_at) SELECT uuid, email, password, created_at FROM users");
                    } else {
                        // If no uuid, we'll need to generate them (shouldn't happen based on previous history but safe)
                        $stmt = self::$pdo->query("SELECT * FROM users");
                        $users = $stmt->fetchAll();
                        $insertStmt = self::$pdo->prepare("INSERT INTO users_new (id, email, password, created_at) VALUES (?, ?, ?, ?)");
                        foreach ($users as $user) {
                            $insertStmt->execute([self::generateUuid(), $user['email'], $user['password'], $user['created_at']]);
                        }
                    }

                    // 3. Swap tables
                    self::$pdo->exec("DROP TABLE users");
                    self::$pdo->exec("ALTER TABLE users_new RENAME TO users");

                    self::$pdo->commit();
                } catch (\Exception $e) {
                    self::$pdo->rollBack();
                    throw $e;
                }
            }
        }

        // Seed default user if not exists
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['brian@olsfamily.com']);
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('tacos123', PASSWORD_BCRYPT);
            $stmt = self::$pdo->prepare("INSERT INTO users (id, email, password) VALUES (?, ?, ?)");
            $stmt->execute([self::generateUuid(), 'brian@olsfamily.com', $hashedPassword]);
        }
    }

    public static function generateUuid(): string
    {
        // v4 UUID: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx where y is one of 8, 9, A, or B
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100 (4)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
