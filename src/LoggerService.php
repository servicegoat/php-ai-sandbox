<?php

namespace App;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerService
{
    private static $logger;

    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger('app');
            // Log to a file, not to the screen
            $logFile = __DIR__ . '/../logs/app.log';
            self::$logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
        }

        return self::$logger;
    }
}
