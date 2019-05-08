<?php

declare(strict_types=1);

namespace Emphloyer;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Logger
{
    /** @var LoggerInterface */
    private static $logger;

    public static function setLogger(LoggerInterface $logger) : void
    {
        self::$logger = $logger;
    }

    public static function getLogger() : LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = new NullLogger();
        }

        return self::$logger;
    }
}
