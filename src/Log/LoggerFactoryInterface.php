<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Log;

use Psr\Log\LoggerInterface as Logger;

interface LoggerFactoryInterface
{
    /**
     * @return Logger
     */
    public function createLogger(): Logger;
}
