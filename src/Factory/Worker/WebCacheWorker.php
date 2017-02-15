<?php

namespace Radebatz\Assetic\Factory\Worker;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\FileAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;
use Radebatz\Assetic\Util\WebUtils;

/**
 * Asset web cache worker.
 *
 * File system based caching with the cache folder integrated into the docroot.
 */
class WebCacheWorker implements WorkerInterface
{

    /**
     * Process.
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        if ($asset instanceof FileAsset) {
            $asset->setTargetPath(WebUtils::getVersionTargetPath($asset));
        }

        return null;
    }
}
