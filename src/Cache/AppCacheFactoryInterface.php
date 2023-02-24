<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use Psr\Cache\CacheItemPoolInterface as Cache;

interface AppCacheFactoryInterface
{
    public function createAppCache(): Cache;
}
