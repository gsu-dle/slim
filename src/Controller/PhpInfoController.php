<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Controller;

use Psr\Http\Message\ResponseInterface      as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Example controller that returns the contents of phpinfo()
 */
class PhpInfoController
{
    /**
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function phpinfo(
        Request $request,
        Response $response
    ): Response {
        ob_start();
        phpinfo();
        $response->getBody()->write(strval(ob_get_contents()));
        ob_end_clean();

        return $response;
    }
}
