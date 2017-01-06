<?php

namespace Radebatz\Assetic\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Assetic\Cache\CacheInterface;

/**
 * PSR-6 asset cache.
 */
class Psr6Cache implements CacheInterface
{
    protected $cachePool;

    /**
     * Create new instance decorating the given PSR-6 cache instance.
     */
    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }


    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->cachePool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($item = $this->cachePool->getItem($key)) {
            return $item->get();
        }

        throw new \RuntimeException(sprintf('There is no cached value for: %s ', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $item = $this->cachePool->getItem($key);
        $item->set($value);
        $this->cachePool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->cachePool->deleteItem($key);
    }
}
