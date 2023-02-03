<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use GAState\Web\Slim\Env;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheFactory implements SessionCacheFactoryInterface, AppCacheFactoryInterface
{
    private string $sessionCacheName;
    private string $appCacheDir;

    /**
     * @var array<string, mixed> $sessionValues
     */
    private array $sessionValues = [];


    /**
     * @param string $sessionCacheName
     * @param string $appCacheDir
     * @param array<string,mixed>|null $sessionValues
     */
    public function __construct(
        string $sessionCacheName,
        string $appCacheDir,
        ?array &$sessionValues = null
    ) {
        $this->sessionCacheName = $sessionCacheName;
        $this->appCacheDir = $appCacheDir;

        if ($sessionValues === null) {
            $sessionValues = &$_SESSION;
        }

        /** @var array<string,mixed>|null $sessionValues */
        $this->sessionValues = $sessionValues ?? [];
    }


    /**
     * @return CacheItemPoolInterface
     */
    public function createSessionCache(): CacheItemPoolInterface
    {
        $cache = $this->sessionValues[$this->sessionCacheName] ?? null;
        if (!$cache instanceof CacheItemPoolInterface) {
            $cache = $this->sessionValues[$this->sessionCacheName] = new ArrayAdapter();
        }

        return $cache;
    }


    /**
     * @return CacheItemPoolInterface
     */
    public function createAppCache(): CacheItemPoolInterface
    {
        return new FilesystemAdapter(directory: $this->appCacheDir);
    }
}
