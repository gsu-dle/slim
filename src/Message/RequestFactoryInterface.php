<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Message;

use Psr\Http\Message\ServerRequestInterface;

interface RequestFactoryInterface
{
    /**
     * Create a request from superglobal values.
     */
    public function createRequestFromGlobals(): ServerRequestInterface;
}
