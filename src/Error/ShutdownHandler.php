<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Error;

use GAState\Web\Slim\Emitter\ResponseEmitterInterface as ResponseEmitter;
use GAState\Web\Slim\Error\ErrorHandler as ErrorHandler;
use GAState\Web\Slim\Exception\ShutdownException as ShutdownException;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class ShutdownHandler
{
    protected PsrRequest $request;
    protected ErrorHandler $errorHandler;
    protected ResponseEmitter $responseEmitter;
    protected bool $displayErrorDetails;
    protected bool $logErrors;
    protected bool $logErrorDetails;


    /**
     * @param PsrRequest $request
     * @param ErrorHandler $errorHandler
     * @param ResponseEmitter $responseEmitter
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     */
    public function __construct(
        PsrRequest $request,
        ErrorHandler $errorHandler,
        ResponseEmitter $responseEmitter,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->responseEmitter = $responseEmitter;
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
    }


    /**
     * @return void
     */
    public function __invoke(): void
    {
        $exception = new ShutdownException($this->request);
        if ($exception->error === null) {
            return;
        }

        $response = $this->errorHandler->__invoke(
            $this->request,
            $exception,
            $this->displayErrorDetails,
            $this->logErrors,
            $this->logErrorDetails
        );

        $this->responseEmitter->emit($response);
    }
}
