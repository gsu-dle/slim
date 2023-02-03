<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Emitter;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $response): bool;
}
