<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Psr\Http\Message\ResponseInterface;

interface RendererInterface
{
    /**
     * @param  ResponseInterface    $response
     * @param  string               $template
     * @param  array<string, mixed> $data
     *
     * @return ResponseInterface
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ): ResponseInterface;
}
