<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use Psr\Container\ContainerInterface;
use DI\Attribute\Inject                               as Inject;
use GAState\Web\Slim\Emitter\ResponseEmitterInterface as ResponseEmitter;
use GAState\Web\Slim\Error\ShutdownHandler            as ShutdownHandler;
use GAState\Web\Slim\Middleware\ErrorMiddleware       as ErrorMiddleware;
use GAState\Web\Slim\Middleware\SessionMiddleware     as SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface           as Request;
use Psr\Http\Message\ResponseInterface                as Response;
use Psr\Http\Server\MiddlewareInterface               as Middleware;
use RuntimeException                                  as RuntimeException;
use Slim\App                                          as SlimApp;
use Slim\Interfaces\RouteCollectorProxyInterface      as SlimRouteContainer;
use Slim\Middleware\BodyParsingMiddleware             as SlimBodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware           as SlimContentLengthMiddleware;
use Slim\Middleware\RoutingMiddleware                 as SlimRoutingMiddleware;

abstract class App
{
    /** @var SlimApp<ContainerInterface> $slimApp */
    private SlimApp $slimApp;
    private ShutdownHandler $shutdownHandler;
    private Request $request;
    private ResponseEmitter $responseEmitter;

    #[Inject]
    protected ?SlimRoutingMiddleware $routingMiddleware = null;
    #[Inject]
    protected ?SlimBodyParsingMiddleware $bodyParsingMiddleware = null;
    #[Inject]
    protected ?SlimContentLengthMiddleware $contentLengthMiddleware = null;
    #[Inject]
    protected ?ErrorMiddleware $errorMiddleware = null;
    #[Inject]
    protected ?SessionMiddleware $sessionMiddleware = null;


    /**
     * @param SlimApp<ContainerInterface> $slimApp
     * @param ShutdownHandler $shutdownHandler
     * @param Request $request
     * @param ResponseEmitter $responseEmitter
     */
    public function __construct(
        SlimApp $slimApp,
        ShutdownHandler $shutdownHandler,
        Request $request,
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
    public function init(): void
    {
        $this->registerShutdownFunction($this->shutdownHandler);

        $this->loadMiddleware([
            SlimBodyParsingMiddleware::class   => $this->bodyParsingMiddleware,
            SessionMiddleware::class           => $this->sessionMiddleware,
            SlimRoutingMiddleware::class       => $this->routingMiddleware,
            ErrorMiddleware::class             => $this->errorMiddleware,
            SlimContentLengthMiddleware::class => $this->contentLengthMiddleware
        ]);

        $this->loadRoutes($this->slimApp);
    }


    /**
     * @return void
     */
    public function run(): void
    {
        $ob_level = ob_get_level();

        if (!ob_start()) {
            throw new RuntimeException('Unable to start output buffer');
        }

        $response = $this->handle($this->request);

        while (ob_get_level() > $ob_level) {
            if (!ob_end_clean()) {
                throw new RuntimeException('Unable to end output buffer');
            }
        }

        $this->emit($response);
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
     * @param array<string,Middleware|null> $middleware
     *
     * @return void
     */
    protected function loadMiddleware(array $middleware): void
    {
        foreach ($middleware as $mw) {
            if ($mw instanceof Middleware) {
                $this->slimApp->addMiddleware($mw);
            }
        }
    }


    /**
     * @param SlimRouteContainer<ContainerInterface> $routes
     *
     * @return void
     */
    abstract protected function loadRoutes(SlimRouteContainer $routes): void;


    /**
     * @param Request $request
     *
     * @return Response
     */
    protected function handle(Request $request): Response
    {
        return $this->slimApp->handle($request);
    }


    /**
     * @param Response $response
     *
     * @return void
     */
    protected function emit(Response $response): void
    {
        if (!ob_start()) {
            throw new RuntimeException('Unable to start output buffer');
        }

        $ob_level = ob_get_level();

        $this->responseEmitter->emit($response);

        while (ob_get_level() > $ob_level) {
            if (!ob_end_clean()) {
                throw new RuntimeException('Unable to end output buffer');
            }
        }

        if (!ob_end_flush()) {
            throw new RuntimeException('Unable to flush output buffer');
        }
    }
}
