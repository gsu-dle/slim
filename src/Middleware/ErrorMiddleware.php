<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Middleware;

use DI\Attribute\Inject                                   as Inject;
use GAState\Web\Slim\Error\ErrorHandler                   as ErrorHandler;
use GAState\Web\Slim\Error\HttpBadRequestHandler          as BadRequestHandler;
use GAState\Web\Slim\Error\HttpForbiddenHandler           as ForbiddenHandler;
use GAState\Web\Slim\Error\HttpInternalServerErrorHandler as InternalServerErrorHandler;
use GAState\Web\Slim\Error\HttpMethodNotAllowedHandler    as MethodNotAllowedHandler;
use GAState\Web\Slim\Error\HttpNotFoundHandler            as NotFoundHandler;
use GAState\Web\Slim\Error\HttpNotImplementedHandler      as NotImplementedHandler;
use GAState\Web\Slim\Error\HttpUnauthorizedHandler        as UnauthorizedHandler;
use Psr\Http\Message\ResponseInterface                    as Response;
use Psr\Http\Message\ResponseFactoryInterface             as ResponseFactory;
use Psr\Http\Message\ServerRequestInterface               as Request;
use Psr\Http\Server\RequestHandlerInterface               as RequestHandler;
use Psr\Log\LoggerInterface                               as Logger;
use Slim\Exception\HttpBadRequestException                as SlimBadRequestException;
use Slim\Exception\HttpForbiddenException                 as SlimForbiddenException;
use Slim\Exception\HttpInternalServerErrorException       as SlimInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException          as SlimMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException                  as SlimNotFoundException;
use Slim\Exception\HttpNotImplementedException            as SlimNotImplementedException;
use Slim\Exception\HttpUnauthorizedException              as SlimUnauthorizedException;
use Slim\Interfaces\CallableResolverInterface             as SlimCallableResolver;
use Slim\Interfaces\ErrorHandlerInterface                 as SlimErrorHandler;
use Slim\Middleware\ErrorMiddleware                       as SlimErrorMiddleware;

class ErrorMiddleware extends SlimErrorMiddleware
{
    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(ErrorHandler::class)]
    protected $defaultErrorHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(BadRequestHandler::class)]
    protected $badRequestHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(ForbiddenHandler::class)]
    protected $forbiddenHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(InternalServerErrorHandler::class)]
    protected $internalServerErrorHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(MethodNotAllowedHandler::class)]
    protected $methodNotAllowedHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(NotFoundHandler::class)]
    protected $notFoundHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(NotImplementedHandler::class)]
    protected $notImplementedHandler = null;

    /** @var SlimErrorHandler|callable|string|null */
    #[Inject(UnauthorizedHandler::class)]
    protected $unauthorizedHandler = null;


    /**
     * @param SlimCallableResolver $callableResolver
     * @param ResponseFactory $responseFactory
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @param Logger $logger
     */
    public function __construct(
        SlimCallableResolver $callableResolver,
        ResponseFactory $responseFactory,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        Logger $logger
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
     * @param Request $request
     * @param RequestHandler $requestHandler
     *
     * @return Response
     */
    public function process(
        Request $request,
        RequestHandler $requestHandler
    ): Response {
        $defaultErrorHandler = $this->getDefaultErrorHandler();

        $this->handlers = array_merge([
            SlimBadRequestException::class          => $this->badRequestHandler          ?? $defaultErrorHandler,
            SlimUnauthorizedException::class        => $this->unauthorizedHandler        ?? $defaultErrorHandler,
            SlimForbiddenException::class           => $this->forbiddenHandler           ?? $defaultErrorHandler,
            SlimNotFoundException::class            => $this->notFoundHandler            ?? $defaultErrorHandler,
            SlimMethodNotAllowedException::class    => $this->methodNotAllowedHandler    ?? $defaultErrorHandler,
            SlimInternalServerErrorException::class => $this->internalServerErrorHandler ?? $defaultErrorHandler,
            SlimNotImplementedException::class      => $this->notImplementedHandler      ?? $defaultErrorHandler,
        ], $this->handlers);

        return parent::process($request, $requestHandler);
    }
}
