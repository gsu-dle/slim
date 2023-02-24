<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Psr\Http\Message\ResponseInterface as Response;

interface RendererInterface
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
    ): Response;


    /**
     * @param string $template
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function renderToString(
        string $template,
        array $data = []
    ): string;
}
