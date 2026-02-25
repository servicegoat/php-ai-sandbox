<?php

namespace App;

use App\Database;

class AuthService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(string $email, string $password): bool
    {
        self::startSession();
        
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_uuid'] = $user['uuid'];
            $_SESSION['user_email'] = $user['email'];
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        self::startSession();
        session_destroy();
    }

    public static function isAuthenticated(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function getCurrentUserUuid(): ?string
    {
        self::startSession();
        return $_SESSION['user_uuid'] ?? null;
    }
}
