<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use Slim\App as SlimApp;

interface SlimAppFactoryInterface
{
    public function createSlimApp(): SlimApp;
}
