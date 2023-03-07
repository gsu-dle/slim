<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Cache;

use Exception;
use GAState\Web\Slim\Cache\AppCacheFactoryInterface as AppCacheFactory;
use PDO                                             as PDO;
use Psr\Cache\CacheItemPoolInterface                as Cache;
use Symfony\Component\Cache\Adapter\PdoAdapter      as PdoAdapter;

class DBAppCacheFactory implements AppCacheFactory
{
    private PDO $pdo;

    /**
     * @var array<string,mixed> $options
     */
    private array $options;


    /**
     * @param PDO $pdo
     * @param array<string,mixed> $options
     */
    public function __construct(
        PDO $pdo,
        array $options = []
    ) {
        $this->pdo = $pdo;
        $this->options = $options;
    }


    /**
     * @param string $namespace
     * @param int $defaultLifetime
     *
     * @return Cache
     */
    public function createAppCache(
        string $namespace = '',
        int $defaultLifetime = 0
    ): Cache {
        $adapter = new PdoAdapter(
            connOrDsn: $this->pdo,
            options: $this->options,
            namespace: $namespace,
            defaultLifetime: $defaultLifetime,
        );

        // TODO: add logic to turn this off from .env
        try {
            $adapter->createTable();
        } catch (Exception) {
        }

        return $adapter;
    }
}
