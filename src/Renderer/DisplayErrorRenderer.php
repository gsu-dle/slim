<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Exception                                               as Exception;
use GAState\Web\Slim\Renderer\DisplayErrorRendererInterface as DisplayErrorRendererInterface;
use Psr\Http\Message\ResponseInterface                      as Response;
use Slim\Error\Renderers\HtmlErrorRenderer                  as SlimHtmlErrorRender;
use Throwable                                               as Throwable;

class DisplayErrorRenderer extends SlimHtmlErrorRender implements DisplayErrorRendererInterface
{
    /**
     * @param Response $response
     * @param string $template
     * @param array<string, mixed> $data
     *
     * @return Response
     */
    public function renderToResponse(
        Response $response,
        string $template,
        array $data = []
    ): Response {
        $response->getBody()->write($this->renderToString($template, $data));
        return $response;
    }


    /**
     * @param string $template
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function renderToString(
        string $template,
        array $data = []
    ): string {
        $exception = $data['exception'] ?? null;
        if (!$exception instanceof Throwable) {
            throw new Exception(); // TODO: replace with specific error
        }
        $displayErrorDetails = ($data['displayErrorDetails'] ?? false) === true;

        return $this->__invoke($exception, $displayErrorDetails);
    }
}
