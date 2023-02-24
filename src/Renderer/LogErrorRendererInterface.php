<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use GAState\Web\Slim\Renderer\RendererInterface as Renderer;
use Slim\Interfaces\ErrorRendererInterface      as SlimErrorRenderer;

interface LogErrorRendererInterface extends Renderer, SlimErrorRenderer
{
}
