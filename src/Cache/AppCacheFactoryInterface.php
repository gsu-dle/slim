<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use Psr\Cache\CacheItemPoolInterface;

interface AppCacheFactoryInterface
{
    public function createAppCache(): CacheItemPoolInterface;
}
