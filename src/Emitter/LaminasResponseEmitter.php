<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Emitter;

use GAState\Web\Slim\Emitter\ResponseEmitterInterface as ResponseEmitter;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter     as LaminasSapiEmitter;
use Psr\Http\Message\ResponseInterface                as Response;

class LaminasResponseEmitter implements ResponseEmitter
{
    protected LaminasSapiEmitter $emitter;


    /**
     * @param LaminasSapiEmitter $emitter
     */
    public function __construct(LaminasSapiEmitter $emitter)
    {
        $this->emitter = $emitter;
    }


    /**
     * @param Response $response
     *
     * @return bool
     */
    public function emit(Response $response): bool
    {
        return $this->emitter->emit($response);
    }
}
