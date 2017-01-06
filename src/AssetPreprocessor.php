<?php

namespace Radebatz\Assetic;

use RuntimeException;
use InvalidArgumentException;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\FileAsset;
use Assetic\Factory\AssetFactory;

/**
 * Asset preprocessor for file assets.
 *
 * Will try to find preprocessor statements in files and expand those.
 */
class AssetPreprocessor
{
    protected $config;

    /**
     */
    public function __construct(array $config = []) {
        $this->config = array_merge([
                'meta' => [],
                'types' => [],
            ],
            $config
        );
    }

    /**
     * Lookup statement pattern for type.
     */
    protected function getStatementPatternForType($type) {
        if (array_key_exists($type, $this->config['statements'])) {
            return $this->config['statements'][$type];
        }

        return null;
    }

    /**
     * Process asset.
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        if (!($asset instanceof FileAsset) || !($ext = pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION)) || !($pattern = $this->getStatementPatternForType($ext))) {
            return null;
        }

        try {
            $asset->load();
        } catch (RuntimeException $e) {
            // happens when the source root resolver kicks in - then the replaced leaf is still pushed through the other workers
            return null;
        }

        $lines = [];
        $statements = [];

        // try to find preprocessor statements and strip out of content
        foreach (explode("\n", $asset->getContent()) as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $statements[] = $matches[1];
            } else {
                $lines[] = $line;
            }
        }

        if (!$statements) {
            // nothing to do here
            return null;
        }

        $asset->setContent(implode("\n", $lines));

        // now things are relative to this asset
        $collection = new AssetCollection();
        $addedSelf = false;
        foreach ($statements as $statement) {
            if (preg_match('/^(require(_tree|_self)?)\s*(.*)$/', $statement, $matches)) {
                $directive = $matches[1];
                // optional file/dir name
                $args = trim($matches[3]);
                if ($args) {
                    // add ./ prefix if missing to make the next if statement simpler :)
                    $args = ('/' == $args[0] || '.' == $args[0]) ? $args : './'.$args;

                    // resolve relative...
                    if ('/' == $args[1] || '.' == $args[1]) {
                        // relative to here
                        $sourceRoot = $asset->getSourceRoot();

                        // resolve to full path and get rid of relative bits
                        $relPath = realpath(sprintf('%s/%s/%s', $sourceRoot, dirname($asset->getSourcePath()), dirname($args)));
                        if (!$relPath) {
                            if (!$factory->isDebug()) {
                                // ignore non existing
                                continue;
                            }

                            throw new InvalidArgumentException(sprintf('Traversing to non existent directory: asset=%s, %s', $asset->getSourcePath(), $args));
                        }
                        if (0 !== strpos($relPath, $sourceRoot)) {
                            if (!$factory->isDebug()) {
                                // ignore non existing
                                continue;
                            }

                            throw new InvalidArgumentException(sprintf('Traversing out of current source root: asset=%s, %s', $asset->getSourcePath(), $args));
                        }

                        // chop of absolute source root and tuck asset at the end again
                        $args = ltrim(sprintf('%s/%s', substr($relPath, strlen($sourceRoot)), basename($args)), '/');
                    } else {
                        // .file!
                    }
                }

                // avoid doing too much (and loops!)
                $key = sprintf('%s:%s', $directive, $args);

                switch ($directive) {
                case 'require':
                    $collection->add($sub = $factory->createAsset($args));
                    foreach ($asset->getFilters() as $filter) {
                        $sub->ensureFilter($filter);
                    }
                    break;

                case 'require_tree':
                    // tree always references a directory
                    $collection->add($factory->createAsset($args.'/*.*', $asset->getFilters()));
                    $collection->add($factory->createAsset($args.'/*/*.*', $asset->getFilters()));

                    break;

                case 'require_self':
                    $collection->add($asset);
                case 'skip_self':
                    $addedSelf = true;
                    break;
                }
            }
        }

        if (!$addedSelf) {
            $collection->add($asset);
        }

        return $collection;
    }
}
