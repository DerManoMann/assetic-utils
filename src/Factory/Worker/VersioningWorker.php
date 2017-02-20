<?php

namespace Radebatz\Assetic\Factory\Worker;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\FileAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;
use Radebatz\Assetic\Util\VersionUtils;

/**
 * Asset versioning worker.
 */
class VersioningWorker implements WorkerInterface
{
    protected $prefix;

    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
    }

    /**
     * Process.
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        VersionUtils::setVersionTargetPath($asset);
        // inject prefix
        $asset->setTargetPath($this->prefix.$asset->getTargetPath());

        return null;
    }
}
