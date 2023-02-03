<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Slim\Error\Renderers\PlainTextErrorRenderer;

class LogErrorRenderer extends PlainTextErrorRenderer implements LogErrorRendererInterface
{
}
