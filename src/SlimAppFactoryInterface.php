<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use Psr\Container\ContainerInterface;
use Slim\App as SlimApp;

interface SlimAppFactoryInterface
{
    /**
     * @return SlimApp<ContainerInterface>
     */
    public function createSlimApp(): SlimApp;
}
