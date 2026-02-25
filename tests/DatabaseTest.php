<?php

namespace Tests;

use App\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class DatabaseTest extends TestCase
{
    private $dbPath;

    protected function setUp(): void
    {
        // Use a temporary database for testing
        $this->dbPath = __DIR__ . '/../database/test_database.sqlite';
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
        
        // We need to trick Database class to use this path or just test the logic
        // Since Database::getConnection() has a hardcoded path, we might need to be careful
        // or mock it. But let's see if we can just test the generateUuid and other logic first.
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function testGenerateUuid()
    {
        $uuid = Database::generateUuid();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
    }

    public function testGetConnection()
    {
        $pdo = Database::getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }
}
