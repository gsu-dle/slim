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
use PDO;
use Psr\Http\Message\ServerRequestInterface     as Request;
use Psr\Log\LoggerInterface                     as Logger;

class DBLoggerFactory implements LoggerFactory
{
    private string $logName;
    private string $logLevel;
    private PDO $pdo;
    private Request $request;


    /**
     * @param string $logName
     * @param string $logLevel
     * @param PDO $pdo
     * @param Request $request
     */
    public function __construct(
        string $logName,
        string $logLevel,
        PDO $pdo,
        Request $request
    ) {
        $this->logName = $logName;
        $this->logLevel = $logLevel;
        $this->pdo = $pdo;
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
        $handler = new DBLogHandler(
            $this->pdo,
            'WebLog',
            [
                'uid',
                'process_id',
                'url',
                'ip',
                'http_method',
                'server',
                'referrer',
                'trace'
            ],
            false,
            MonologLevel::fromName($this->logLevel)->value
        );

        $handler->setFormatter(new MonologLineFormatter(
            format: "%message%\n",
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
