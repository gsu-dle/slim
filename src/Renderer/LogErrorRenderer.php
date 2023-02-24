<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use GAState\Web\Slim\Renderer\LogErrorRendererInterface as LogErrorRendererInterface;
use Psr\Http\Message\ResponseInterface                  as Response;
use Slim\Error\Renderers\PlainTextErrorRenderer         as SlimPlainTextErrorRenderer;
use Throwable                                           as Throwable;

class LogErrorRenderer extends SlimPlainTextErrorRenderer implements LogErrorRendererInterface
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
        $data = array_values($data);
        foreach ($data as $idx => $value) {
            if (!(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || $value === null)) {
                unset($data[$idx]);
            }
        }

        /** @var array<bool|float|int|string|null> $data */
        return sprintf($template, ...$data);
    }


    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($displayErrorDetails) {
            $template = "%s: %s";
            $data = [
                'class' => get_class($exception),
                'msg' => htmlentities($exception->getMessage())
            ];
        } else {
            $template = "%s";
            $data = [
                'msg' => htmlentities($exception->getMessage())
            ];
        }

        return $this->renderToString($template, $data);
    }
}
