<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Error;

use DI\Attribute\Inject;
use GAState\Web\Slim\Renderer\DisplayErrorRendererInterface as DisplayErrorRenderer;
use GAState\Web\Slim\Renderer\LogErrorRendererInterface     as LogErrorRenderer;
use Psr\Http\Message\ResponseFactoryInterface               as PsrResponseFactory;
use Psr\Log\LoggerInterface                                 as PsrLogger;
use Slim\Handlers\ErrorHandler                              as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface               as SlimCallableResolver;

class ErrorHandler extends SlimErrorHandler
{
    protected string $defaultErrorRendererContentType = 'text/html';
    #[Inject(DisplayErrorRenderer::class)]
    protected $defaultErrorRenderer = DisplayErrorRenderer::class;
    #[Inject(LogErrorRenderer::class)]
    protected $logErrorRenderer = LogErrorRenderer::class;


    /**
     * @param SlimCallableResolver $callableResolver
     * @param PsrResponseFactory $responseFactory
     * @param PsrLogger $logger
     */
    public function __construct(
        SlimCallableResolver $callableResolver,
        PsrResponseFactory $responseFactory,
        PsrLogger $logger
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
    }


    /**
     * @return void
     */
    protected function writeToErrorLog(): void
    {
        $renderer = $this->callableResolver->resolve($this->logErrorRenderer);
        $error = $renderer($this->exception, $this->logErrorDetails);
        $this->logger->error($error);
    }
}
