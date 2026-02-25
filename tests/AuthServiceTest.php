<?php

namespace Tests;

use App\AuthService;
use App\Database;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Use a test user
        $this->pdo = Database::getConnection();
        $this->pdo->exec("DELETE FROM users WHERE email = 'test_auth@example.com'");
        
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $id = Database::generateUuid();
        $stmt = $this->pdo->prepare("INSERT INTO users (id, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$id, 'test_auth@example.com', $password]);

        // Mock session if needed or just use it (PHPUnit might have issues with session_start)
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email = 'test_auth@example.com'");
        $_SESSION = [];
    }

    public function testLoginSuccess()
    {
        $result = AuthService::login('test_auth@example.com', 'password123');
        $this->assertTrue($result);
        $this->assertEquals('test_auth@example.com', $_SESSION['user_email']);
        $this->assertArrayHasKey('user_id', $_SESSION);
    }

    public function testLoginFailure()
    {
        $result = AuthService::login('test_auth@example.com', 'wrongpassword');
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testIsAuthenticated()
    {
        $this->assertFalse(AuthService::isAuthenticated());
        $_SESSION['user_id'] = 'some-uuid';
        $this->assertTrue(AuthService::isAuthenticated());
    }

    public function testLogout()
    {
        $_SESSION['user_id'] = 'some-uuid';
        AuthService::logout();
        // session_destroy() doesn't clear $_SESSION in the current script execution, 
        // but it clears the session data on disk. 
        // However, our AuthService::logout calls session_destroy.
        // In a test environment, we might need to manually clear $_SESSION.
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testGetCurrentUserUuid()
    {
        $this->assertNull(AuthService::getCurrentUserUuid());
        $_SESSION['user_id'] = 'some-uuid';
        $this->assertEquals('some-uuid', AuthService::getCurrentUserUuid());
    }
}
