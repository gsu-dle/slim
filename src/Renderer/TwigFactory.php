<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Renderer;

use Slim\Views\Twig as SlimTwigView;

class TwigFactory
{
    /**
     * @var string|array<string> $paths
     */
    private string|array $paths;


    /**
     * @var array<string, mixed> $settings
     */
    private array $settings;


    /**
     * @param string|array<string> $paths
     * @param array<string, mixed> $settings
     */
    public function __construct(string|array $paths, array $settings = [])
    {
        $this->paths = $paths;
        $this->settings = $settings;
    }


    public function create(): SlimTwigView
    {
        return SlimTwigView::create($this->paths, $this->settings);
    }
}
