<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

class TwigRenderer implements RendererInterface
{
    private Twig $twig;


    /**
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }


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
    ): ResponseInterface {
        return $this->twig->render($response, $template, $data);
    }
}
