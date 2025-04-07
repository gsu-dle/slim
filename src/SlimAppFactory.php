<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use DI\Bridge\Slim\Bridge                   as DISlimBridge;
use DI\Container                            as DIContainer;
use Psr\Container\ContainerInterface;
use Slim\App                                as SlimApp;
use Slim\Interfaces\RouteCollectorInterface as SlimRouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface    as SlimRouteParserInterface;
use Slim\Interfaces\RouteResolverInterface  as SlimRouteResolverInterface;

class SlimAppFactory implements SlimAppFactoryInterface
{
    private DIContainer $container;
    private string $baseURI;


    /**
     * @param DIContainer $container
     * @param string $baseURI
     */
    public function __construct(
        DIContainer $container,
        string $baseURI
    ) {
        $this->container = $container;
        $this->baseURI = $baseURI;
    }


    /**
     * @return SlimApp<ContainerInterface>
     */
    public function createSlimApp(): SlimApp
    {
        $app = DISlimBridge::create($this->container);
        $app->setBasePath($this->baseURI);
        $this->container->set(SlimRouteResolverInterface::class, $app->getRouteResolver());
        $this->container->set(SlimRouteCollectorInterface::class, $app->getRouteCollector());
        $this->container->set(SlimRouteParserInterface::class, $app->getRouteCollector()->getRouteParser());
        return $app;
    }
}
