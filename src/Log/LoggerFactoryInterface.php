<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Log;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    /**
     * @return LoggerInterface
     */
    public function createLogger(): LoggerInterface;
}
