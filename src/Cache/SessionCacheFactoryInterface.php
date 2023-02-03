<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use Psr\Cache\CacheItemPoolInterface;

interface SessionCacheFactoryInterface
{
    public function createSessionCache(): CacheItemPoolInterface;
}
