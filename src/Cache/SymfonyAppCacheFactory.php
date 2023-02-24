<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use GAState\Web\Slim\Cache\AppCacheFactoryInterface   as AppCacheFactory;
use Psr\Cache\CacheItemPoolInterface                  as Cache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as FilesystemAdapter;

class SymfonyAppCacheFactory implements AppCacheFactory
{
    private string $appCacheDir;


    /**
     * @param string $appCacheDir
     */
    public function __construct(string $appCacheDir)
    {
        $this->appCacheDir = $appCacheDir;
    }


    /**
     * @return Cache
     */
    public function createAppCache(): Cache
    {
        return new FilesystemAdapter(directory: $this->appCacheDir);
    }
}
