<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use Psr\Cache\CacheItemPoolInterface as Cache;

interface AppCacheFactoryInterface
{
    /**
     * @param string $namespace
     * @param int $defaultLifetime
     *
     * @return Cache
     */
    public function createAppCache(
        string $namespace = '',
        int $defaultLifetime = 0
    ): Cache;
}
