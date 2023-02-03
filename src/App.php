<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use DI\Attribute\Inject;
use GAState\Web\Slim\Emitter\ResponseEmitterInterface as ResponseEmitter;
use GAState\Web\Slim\Error\ShutdownHandler            as ShutdownHandler;
use GAState\Web\Slim\Middleware\ErrorMiddleware       as ErrorMiddleware;
use GAState\Web\Slim\Middleware\SessionMiddleware     as SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface           as PsrServerRequest;
use Psr\Http\Message\ResponseInterface                as PsrResponse;
use Psr\Http\Server\MiddlewareInterface               as PsrMiddleware;
use Slim\App                                          as SlimApp;
use Slim\Interfaces\RouteCollectorProxyInterface      as SlimRouteContainer;
use Slim\Middleware\BodyParsingMiddleware             as SlimBodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware           as SlimContentLengthMiddleware;
use Slim\Middleware\RoutingMiddleware                 as SlimRoutingMiddleware;

abstract class App
{
    private SlimApp $slimApp;
    private ShutdownHandler $shutdownHandler;
    private PsrServerRequest $request;
    private ResponseEmitter $responseEmitter;

    #[Inject]
    private ?SlimRoutingMiddleware $routingMiddleware = null;
    #[Inject]
    private ?SlimBodyParsingMiddleware $bodyParsingMiddleware = null;
    #[Inject]
    private ?SlimContentLengthMiddleware $contentLengthMiddleware = null;
    #[Inject]
    private ?ErrorMiddleware $errorMiddleware = null;
    #[Inject]
    private ?SessionMiddleware $sessionMiddleware = null;


    /**
     * @param SlimApp $slimApp
     * @param ShutdownHandler $shutdownHandler
     * @param PsrServerRequest $request
     * @param ResponseEmitter $responseEmitter
     */
    public function __construct(
        SlimApp $slimApp,
        ShutdownHandler $shutdownHandler,
        PsrServerRequest $request,
        ResponseEmitter $responseEmitter
    ) {
        $this->slimApp = $slimApp;
        $this->shutdownHandler = $shutdownHandler;
        $this->request = $request;
        $this->responseEmitter = $responseEmitter;
    }


    /**
     * @return void
     */
    public function run(): void
    {
        ob_start();

        $this->registerShutdownFunction($this->shutdownHandler);

        $this->loadMiddleware([
            SlimBodyParsingMiddleware::class   => $this->bodyParsingMiddleware,
            SessionMiddleware::class           => $this->sessionMiddleware,
            SlimRoutingMiddleware::class       => $this->routingMiddleware,
            ErrorMiddleware::class             => $this->errorMiddleware,
            SlimContentLengthMiddleware::class => $this->contentLengthMiddleware
        ]);

        $this->loadRoutes($this->slimApp);

        $response = $this->handle($this->request);

        ob_clean();

        $this->emit($response);

        ob_end_flush();
    }


    /**
     * @param ShutdownHandler $shutdownHandler
     *
     * @return void
     */
    protected function registerShutdownFunction(ShutdownHandler $shutdownHandler): void
    {
        register_shutdown_function($shutdownHandler);
    }


    /**
     * @param array<string, PsrMiddleware|null> $middleware
     *
     * @return void
     */
    protected function loadMiddleware(array $middleware): void
    {
        foreach ($middleware as $mw) {
            if ($mw instanceof PsrMiddleware) {
                $this->slimApp->addMiddleware($mw);
            }
        }
    }


    /**
     * @param SlimRouteContainer $routes
     *
     * @return void
     */
    abstract protected function loadRoutes(SlimRouteContainer $routes): void;


    /**
     * @param PsrServerRequest $request
     *
     * @return PsrResponse
     */
    protected function handle(PsrServerRequest $request): PsrResponse
    {
        return $this->slimApp->handle($request);
    }


    /**
     * @param PsrResponse $response
     *
     * @return void
     */
    protected function emit(PsrResponse $response): void
    {
        $this->responseEmitter->emit($response);
    }
}
