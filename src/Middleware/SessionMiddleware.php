<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Middleware;

use GAState\Web\Slim\Env;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    /**
     * @param Request $request
     * @param RequestHandler $handler
     *
     * @return Response
     */
    public function process(
        Request $request,
        RequestHandler $handler
    ): Response {
        $options = array_change_key_case(Env::getValues(Env::SESSION_PREFIX, [
            'USE_STRICT_MODE'        => 1,
            'COOKIE_HTTPONLY'        => 1,
            'COOKIE_SECURE'          => $request->getUri()->getScheme() === 'https' ? 1 : 0,
            'COOKIE_SAMESITE'        => 'Lax',
            'COOKIE_LIFETIME'        => 7200,
            'GC_MAXLIFETIME'         => 7200,
            'SID_LENGTH'             => 48,
            'SID_BITS_PER_CHARACTER' => 5
        ]));

        session_start($options);

        $response = $handler->handle($request);

        session_write_close();

        return $response;
    }
}
