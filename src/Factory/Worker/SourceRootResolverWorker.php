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
 *
 * The default resolver implementations try to build an absolute filename from root and path and consider a
 * root directory resolved if the resulting file exists (file) or matches any existing file (glob).
 *
 * Signature for the file resolver:
 *
 * <code>resolver(AssetInterface $asset, $root)</code>.
 *
 * Expected return values are either the full resolved filename or null.
 *
 *
 * Signature for the glob resolver:
 *
 * <code>resolver(AssetInterface $asset, $glob, $root)</code>.
 *
 * Expected return values are either a final source root value to be used or null.
 */
class SourceRootResolverWorker implements WorkerInterface {
    protected $root;
    protected $fileResolver;
    protected $globResolver;

    /**
     * Create worker with the given root dirs.
     *
     * @param array List of root directories for relative file sources.
     * @param callable $fileResolver Custom file resolver.
     * @param callable $globResolver Custom glob resolver.
     */
    public function __construct(array $root = [], $fileResolver = null, $globResolver = null) {
        $this->root = $root;
        if ($fileResolver && is_callable($fileResolver)) {
            $this->fileResolver = $fileResolver;
        } else {
            $this->fileResolver = function ($asset, $root) {
                $filename = sprintf('%s/%s', $root, $asset->getSourcePath());
                if (realpath($filename)) {
                    return $filename;
                }

                return null;
            };
        }
        if ($globResolver && is_callable($globResolver)) {
            $this->globResolver = $globResolver;
        } else {
            $this->globResolver = function ($asset, $glob, $root) {
                $rglob = sprintf('%s/%s', $root, ltrim($glob, '/'));

                return glob($rglob) ? $root : null;
            };
        }
    }

    /**
     * Process file asset.
     */
    protected function processFileAsset(FileAsset $asset, AssetFactory $factory) {
        if (!$asset->getSourceRoot()) {
            $fileResolver = $this->fileResolver;
            $path = $asset->getSourcePath();
            foreach ($this->root as $root) {
                if ($filename = $fileResolver($asset, $root)) {
                    $asset = $factory->createAsset(
                        $filename,
                        $asset->getFilters(),
                        [
                            'root' => $root,
                        ]
                    );
                    return $asset;
                }
            }
        }

        return null;
    }

    /**
     * Process glob asset.
     */
    protected function processGlobAsset(GlobAsset $asset, AssetFactory $factory) {
        if (!$asset->getSourceRoot()) {
            // access globs without initializing the asset....
            $rc = new ReflectionClass($asset);
            if ($rp = $rc->getProperty('globs')) {
                $rp->setAccessible(true);
                $globs = $rp->getValue($asset);

                $globResolver = $this->globResolver;
                $sourceRoot = null;
                foreach ($globs as $glob) {
                    $glob = VarUtils::resolve($glob, $asset->getVars(), $asset->getValues());

                    foreach ($this->root as $root) {
                        if ($sourceRoot = $globResolver($asset, $glob, $root)) {
                            break;
                        }
                    }

                    if ($sourceRoot) {
                        // use first resolved
                        break;
                    }
                }

                if ($sourceRoot) {
                    return $factory->createAsset(
                        array_map(function ($glob) use ($sourceRoot) { return sprintf('%s/%s', $sourceRoot, ltrim($glob, '/')); }, $globs),
                        $asset->getFilters(),
                        [
                            'root' => $sourceRoot,
                        ]
                    );
                }
            }
        }

        return null;
    }

    /**
     * Process asset.
     */
    protected function processAsset(AssetInterface $asset, AssetFactory $factory) {
        if ($asset instanceof FileAsset) {
            return $this->processFileAsset($asset, $factory);
        } elseif ($asset instanceof GlobAsset) {
            return $this->processGlobAsset($asset, $factory);
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
                if ($processed = $this->processAsset($leaf, $factory)) {
                    $collection->add($processed);
                    $changed = true;
                } else {
                    $collection->add($leaf);
                }
            }

            return $changed ? $collection : null;
        }

        return $this->processAsset($asset, $factory);
    }
}
