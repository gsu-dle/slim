<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Log;

use RuntimeException;
use GAState\Web\Slim\Env;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @return LoggerInterface
     */
    public function createLogger(): LoggerInterface
    {
        $logName = Env::getString(Env::LOG_NAME);
        $logFile = Env::getString(Env::LOG_FILE);
        $logLevel = Env::getString(Env::LOG_LEVEL);
        if (!in_array($logLevel, Level::NAMES, true)) {
            throw new RuntimeException("Invalid log level: '{$logLevel}'");
        }

        $logger = new Logger($logName);
        $processor = new UidProcessor();
        $handler = new StreamHandler($logFile, $logLevel);

        return $logger
            ->pushProcessor($processor)
            ->pushHandler($handler);
    }
}
