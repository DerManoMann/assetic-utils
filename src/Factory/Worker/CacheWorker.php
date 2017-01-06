<?php

namespace Radebatz\Assetic\Factory\Worker;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Asset\FileAsset;
use Assetic\Cache\CacheInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;

/**
 * Asset cache worker.
 *
 * Decorates file assets with <code>AssetCache</code> to cache each individual asset.
 * The cache worker should always be the last worker added to the factory.
 */
class CacheWorker implements WorkerInterface
{
    protected $cache;

    /**
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Process.
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        if ($asset instanceof FileAsset) {
            return new AssetCache($asset, $this->cache);
        }

        return null;
    }
}
