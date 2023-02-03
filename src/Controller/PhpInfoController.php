<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpInfoController
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function phpinfo(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        ob_start();
        phpinfo();
        $response->getBody()->write(strval(ob_get_contents()));
        ob_end_clean();

        return $response;
    }
}
