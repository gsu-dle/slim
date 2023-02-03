<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Emitter;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;

class LaminasResponseEmitter implements ResponseEmitterInterface
{
    protected SapiEmitter $sapiEmitter;


    /**
     * @param SapiEmitter $sapiEmitter
     */
    public function __construct(SapiEmitter $sapiEmitter)
    {
        $this->sapiEmitter = $sapiEmitter;
    }


    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function emit(ResponseInterface $response): bool
    {
        return $this->sapiEmitter->emit($response);
    }
}
