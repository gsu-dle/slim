<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Middleware;

use GAState\Web\Slim\Session\AppSessionInterface as AppSession;
use Psr\Http\Message\ResponseInterface           as Response;
use Psr\Http\Message\ServerRequestInterface      as Request;
use Psr\Http\Server\MiddlewareInterface          as Middleware;
use Psr\Http\Server\RequestHandlerInterface      as RequestHandler;

class SessionMiddleware implements Middleware
{
    private AppSession $session;


    public function __construct(AppSession $session)
    {
        $this->session = $session;
    }


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
        $this->session->startSession([
            'cookie_secure' => $request->getUri()->getScheme() === 'https' ? 1 : 0,
        ]);
        $request->withAttribute('session', $this->session);

        $response = $handler->handle($request);

        $this->session->endSession();

        return $response;
    }
}
