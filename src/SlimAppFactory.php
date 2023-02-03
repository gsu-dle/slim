<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use Slim\App as SlimApp;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;

class SlimAppFactory implements SlimAppFactoryInterface
{
    private Container $container;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @return SlimApp
     */
    public function createSlimApp(): SlimApp
    {
        $app = Bridge::create($this->container);
        $app->setBasePath(Env::getString(Env::BASE_URI));
        $this->container->set(RouteResolverInterface::class, $app->getRouteResolver());
        $this->container->set(RouteCollectorInterface::class, $app->getRouteCollector());
        $this->container->set(RouteParserInterface::class, $app->getRouteCollector()->getRouteParser());
        return $app;
    }
}
