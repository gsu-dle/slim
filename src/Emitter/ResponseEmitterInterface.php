<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Emitter;

use Psr\Http\Message\ResponseInterface as Response;

interface ResponseEmitterInterface
{
    public function emit(Response $response): bool;
}
