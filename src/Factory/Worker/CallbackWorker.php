<?php

namespace Radebatz\Assetic\Factory\Worker;

use InvalidArgumentException;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;

/**
 * Asset worker delegating to a callback.
 */
class WebCacheWorker implements WorkerInterface
{
    protected $callback;

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Need a callable');
        }

        $this->callback = $callback;
    }

    /**
     * {@inheritodc}
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        return call_user_func($this->callback, $asset, $factory);
    }
}
