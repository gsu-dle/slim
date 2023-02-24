<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Exception;

use Psr\Http\Message\ServerRequestInterface         as Request;
use Slim\Exception\HttpInternalServerErrorException as HttpServerError;
use Throwable                                       as Throwable;

class ShutdownException extends HttpServerError
{
    /**
     * @var array<string,string|int>|null $error
     */
    public readonly ?array $error;
    public readonly string $errorFile;
    public readonly int $errorLine;
    public readonly string $errorMessage;
    public readonly int $errorType;

    /**
     * @param Request $request
     * @param string|null      $message
     * @param Throwable|null   $previous
     */
    public function __construct(
        Request $request,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        $this->error        = $error = error_get_last();
        $this->errorMessage = is_string($error['message'] ?? null) ? strval($error['message']) : '';
        $this->errorFile    = is_string($error['file']    ?? null) ? strval($error['file'])    : '';
        $this->errorLine    = is_int($error['line'] ?? null) ? intval($error['line']) : 0;
        $this->errorType    = is_int($error['type'] ?? null) ? intval($error['type']) : 0;

        parent::__construct($request, $message, $previous);
    }
}
