<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Middleware;

use DI\Attribute\Inject;
// PSR interfaces
use Psr\Http\Message\ResponseInterface        as PsrResponse;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactory;
use Psr\Http\Message\ServerRequestInterface   as PsrServerRequest;
use Psr\Http\Server\RequestHandlerInterface   as PsrRequestHandler;
use Psr\Log\LoggerInterface                   as PsrLogger;
// GSU error handlers
use GAState\Web\Slim\Error\ErrorHandler                   as ErrorHandler;
use GAState\Web\Slim\Error\HttpBadRequestHandler          as BadRequestHandler;
use GAState\Web\Slim\Error\HttpForbiddenHandler           as ForbiddenHandler;
use GAState\Web\Slim\Error\HttpInternalServerErrorHandler as InternalServerErrorHandler;
use GAState\Web\Slim\Error\HttpMethodNotAllowedHandler    as MethodNotAllowedHandler;
use GAState\Web\Slim\Error\HttpNotFoundHandler            as NotFoundHandler;
use GAState\Web\Slim\Error\HttpNotImplementedHandler      as NotImplementedHandler;
use GAState\Web\Slim\Error\HttpUnauthorizedHandler        as UnauthorizedHandler;
// Slim exceptions
use Slim\Exception\HttpBadRequestException          as BadRequestException;
use Slim\Exception\HttpForbiddenException           as ForbiddenException;
use Slim\Exception\HttpInternalServerErrorException as InternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException    as MethodNotAllowedException;
use Slim\Exception\HttpNotFoundException            as NotFoundException;
use Slim\Exception\HttpNotImplementedException      as NotImplementedException;
use Slim\Exception\HttpUnauthorizedException        as UnauthorizedException;
// Slim
use Slim\Interfaces\CallableResolverInterface as SlimCallableResolver;
use Slim\Interfaces\ErrorHandlerInterface     as SlimErrorHandler;
use Slim\Middleware\ErrorMiddleware           as SlimErrorMiddleware;

class ErrorMiddleware extends SlimErrorMiddleware
{
    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(ErrorHandler::class)]
    protected $defaultErrorHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(BadRequestHandler::class)]
    private $badRequestHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(ForbiddenHandler::class)]
    private $forbiddenHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(InternalServerErrorHandler::class)]
    private $internalServerErrorHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(MethodNotAllowedHandler::class)]
    private $methodNotAllowedHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(NotFoundHandler::class)]
    private $notFoundHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(NotImplementedHandler::class)]
    private $notImplementedHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(UnauthorizedHandler::class)]
    private $unauthorizedHandler = null;


    /**
     * @param SlimCallableResolver $callableResolver
     * @param PsrResponseFactory $responseFactory
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @param PsrLogger $logger
     */
    public function __construct(
        SlimCallableResolver $callableResolver,
        PsrResponseFactory $responseFactory,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        PsrLogger $logger
    ) {
        parent::__construct(
            $callableResolver,
            $responseFactory,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
            $logger
        );
    }


    /**
     * @param PsrServerRequest $request
     * @param PsrRequestHandler $requestHandler
     *
     * @return PsrResponse
     */
    public function process(
        PsrServerRequest $request,
        PsrRequestHandler $requestHandler
    ): PsrResponse {
        $defaultErrorHandler = $this->getDefaultErrorHandler();

        $this->handlers = array_merge([
            BadRequestException::class          => $this->badRequestHandler          ?? $defaultErrorHandler,
            UnauthorizedException::class        => $this->unauthorizedHandler        ?? $defaultErrorHandler,
            ForbiddenException::class           => $this->forbiddenHandler           ?? $defaultErrorHandler,
            NotFoundException::class            => $this->notFoundHandler            ?? $defaultErrorHandler,
            MethodNotAllowedException::class    => $this->methodNotAllowedHandler    ?? $defaultErrorHandler,
            InternalServerErrorException::class => $this->internalServerErrorHandler ?? $defaultErrorHandler,
            NotImplementedException::class      => $this->notImplementedHandler      ?? $defaultErrorHandler,
        ], $this->handlers);

        return parent::process($request, $requestHandler);
    }
}
