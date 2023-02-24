<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig                    as SlimTwigView;

class TwigRenderer implements RendererInterface
{
    private SlimTwigView $twig;


    /**
     * @param SlimTwigView $twig
     */
    public function __construct(SlimTwigView $twig)
    {
        $this->twig = $twig;
    }


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
        return $this->twig->render($response, $template, $data);
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
        return $this->twig->getEnvironment()->render($template, $data);
    }
}
