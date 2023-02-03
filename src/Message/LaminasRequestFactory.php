<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Message;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

class LaminasRequestFactory implements RequestFactoryInterface
{
    public function createRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }
}
