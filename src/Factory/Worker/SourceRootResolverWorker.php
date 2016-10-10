<?php

namespace Radebatz\Assetic\Factory\Worker;

use ReflectionClass;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;
use Assetic\Util\VarUtils;

/**
 * Worker to resolve the file/glob asset source root in case we have multiple relative root dirs.
 */
class SourceRootResolverWorker implements WorkerInterface {
    protected $root;

    /**
     * Create worker with the given root dirs.
     */
    public function __construct(array $root = []) {
        $this->root = $root;
    }

    /**
     * Process file asset.
     */
    protected function processFileAsset(FileAsset $asset) {
        if (!$asset->getSourceRoot()) {
            $path = $asset->getSourcePath();
            foreach ($this->root as $root) {
                $filename = sprintf('%s/%s', $root, $asset->getSourcePath());
                if (realpath($filename)) {
                    return new FileAsset($filename, $asset->getFilters(), $root, $path);
                }
            }
        }

        return null;
    }

    /**
     * Process glob asset.
     */
    protected function processGlobAsset(GlobAsset $asset) {
        if (!$asset->getSourceRoot()) {
            // access globs without initializing the asset....
            $rc = new ReflectionClass($asset);
            if ($rp = $rc->getProperty('globs')) {
                $rp->setAccessible(true);
                $globs = $rp->getValue($asset);

                $sourceRoot = null;
                foreach ($globs as $glob) {
                    $glob = VarUtils::resolve($glob, $asset->getVars(), $asset->getValues());

                    // try to find a sourceRoot for first glob
                    if (!glob($glob)) {
                        $glob = ltrim($glob, '/');
                        foreach ($this->root as $root) {
                            $rglob = sprintf('%s/%s', $root, $glob);
                            if (glob($rglob)) {
                                $sourceRoot = $root;
                                break;
                            }
                        }
                    }
                }

                if ($sourceRoot) {
                    return new GlobAsset(
                        array_map(function ($glob) use ($sourceRoot) { return sprintf('%s/%s', $sourceRoot, ltrim($glob, '/')); }, $globs),
                        $asset->getFilters(),
                        $sourceRoot
                    );
                }
            }
        }

        return null;
    }

    /**
     * Process asset.
     */
    protected function processAsset(AssetInterface $asset) {
        if ($asset instanceof FileAsset) {
            return $this->processFileAsset($asset);
        } elseif ($asset instanceof GlobAsset) {
            return $this->processGlobAsset($asset);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        if ($asset instanceof AssetCollection) {
            $collection = new AssetCollection();
            $changed = false;

            // need to look into this to be able to grab GlobAssets before they are resolved
            foreach ($asset->all() as $leaf) {
                if ($processed = $this->processAsset($leaf)) {
                    $collection->add($processed);
                    $changed = true;
                } else {
                    $collection->add($leaf);
                }
            }

            return $changed ? $collection : null;
        }

        return $this->processAsset($asset);
    }
}
