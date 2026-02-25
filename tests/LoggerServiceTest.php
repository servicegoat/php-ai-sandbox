<?php

namespace Tests;

use App\LoggerService;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;

class LoggerServiceTest extends TestCase
{
    public function testGetLogger()
    {
        $logger = LoggerService::getLogger();
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('app', $logger->getName());
        
        // Ensure it's a singleton
        $logger2 = LoggerService::getLogger();
        $this->assertSame($logger, $logger2);
    }
}
