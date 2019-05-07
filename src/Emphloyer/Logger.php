<?php

namespace Emphloyer;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Logger
{
    private static $logger;

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    public static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = new NullLogger();
        }

        return self::$logger;
    }
}