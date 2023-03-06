<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Log;

use GAState\Web\Slim\Log\LoggerFactoryInterface as LoggerFactory;
use RuntimeException                            as RuntimeException;
use Monolog\Formatter\LineFormatter             as MonologLineFormatter;
use Monolog\Handler\StreamHandler               as MonologStreamHandler;
use Monolog\Level                               as MonologLevel;
use Monolog\Logger                              as MonologLogger;
use Monolog\Processor\ProcessIdProcessor        as MonologProcessIdProcessor;
use Monolog\Processor\UidProcessor              as MonologUidProcessor;
use Monolog\Processor\WebProcessor              as MonologWebProcessor;
use Psr\Http\Message\ServerRequestInterface     as Request;
use Psr\Log\LoggerInterface                     as Logger;

class FileLoggerFactory implements LoggerFactory
{
    private string $logName;
    private string $logFile;
    private string $logLevel;
    private Request $request;


    /**
     * @param string $logName
     * @param string $logFile
     * @param string $logLevel
     * @param Request $request
     */
    public function __construct(
        string $logName,
        string $logFile,
        string $logLevel,
        Request $request
    ) {
        $this->logName = $logName;
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        $this->request = $request;
    }


    /**
     * @return Logger
     */
    public function createLogger(): Logger
    {
        if (!in_array($this->logLevel, MonologLevel::NAMES, true)) {
            throw new RuntimeException("Invalid log level: '{$this->logLevel}'");
        }

        $logger = new MonologLogger($this->logName);
        $handler = new MonologStreamHandler($this->logFile, $this->logLevel);
        $handler->setFormatter(new MonologLineFormatter(
            format: "[%datetime%] [%channel%:%level_name%] %message% %context% %extra%\n",
            dateFormat: null,
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true,
            includeStacktraces: false
        ));

        return $logger
            ->pushProcessor(new MonologWebProcessor($this->request->getServerParams()))
            ->pushProcessor(new MonologProcessIdProcessor())
            ->pushProcessor(new MonologUidProcessor())
            ->pushHandler($handler);
    }
}
