<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Request;

use GAState\Web\Slim\Request\GlobalRequestFactoryInterface as GlobalRequestFactory;
use Laminas\Diactoros\ServerRequestFactory                 as LaminasServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface                as Request;

class LaminasGlobalRequestFactory implements GlobalRequestFactory
{
    /**
     * Create a request from superglobal values.
     *
     * @param array<int|string,mixed>|null $server $_SERVER superglobal
     * @param array<int|string,mixed>|null $query $_GET superglobal
     * @param array<int|string,mixed>|null $body $_POST superglobal
     * @param array<int|string,mixed>|null $cookies $_COOKIE superglobal
     * @param array<int|string,mixed>|null $files $_FILES superglobal
     *
     * @return Request
     */
    public function createRequestFromGlobals(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $cookies = null,
        ?array $files = null,
    ): Request {
        return LaminasServerRequestFactory::fromGlobals(
            server: $server,
            query: $query,
            body: $body,
            cookies: $cookies,
            files: $files
        );
    }
}
