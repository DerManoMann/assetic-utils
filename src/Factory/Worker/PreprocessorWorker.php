<?php

namespace Radebatz\Assetic\Factory\Worker;

use InvalidArgumentException;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\FileAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;

/**
 * Asset preprocessor worker.
 */
class PreprocessorWorker implements WorkerInterface
{
    protected $config;
    protected $context;
    protected $processed;

    /**
     */
    public function __construct(array $config = [], array $context = []) {
        $this->config = array_merge([
                'meta' => [],
                'types' => [],
            ],
            $config['preprocessor']
        );
        $this->context = $context;
        $this->processed = [];
    }

    /**
     * Lookup preprocessor directive pattern for type.
     */
    protected function getPatternForType($type) {
        if (array_key_exists($type, $this->config['types'])) {
            return $this->config['types'][$type];
        }

        return null;
    }

    /**
     * Process.
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        if (!($asset instanceof FileAsset) || !($ext = pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION)) || !($pattern = $this->getPatternForType($ext))) {
            return null;
        }

        $asset->load();

        $lines = [];
        $statements = [];
        // try to find preprocessor statements
        foreach (explode("\n", $asset->getContent()) as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $statements[] = $matches[1];
            } else {
                $lines[] = $line;
            }
        }

        if (!$statements) {
            return null;
        }

        // update with content stripped of the statements
        $asset->setContent(implode("\n", $lines));

        // need the module again as now things are relative to this asset - sigh
        $token = explode('/', $asset->getSourcePath());
        $module = $token[0];
        // need a factory in the context of the module
        $factory = $this->assetFactoryFactory->getFactory($module);

        $collection = new AssetCollection();
        foreach ($statements as $statement) {
            if (preg_match('/^(require(_tree|_self)?)\s*(.*)$/', $statement, $matches)) {
                $directive = $matches[1];
                $args = trim($matches[3]);
                if ($args) {
                    // add ./ prefix if missing
                    $args = ('/' == $args[0] || '.' == $args[0]) ? $args : './'.$args;

                    // resolve relative...
                    if ('/' == $args[1] || '.' == $args[1]) {
                        $sourceRoot = $asset->getSourceRoot();

                        // resolve to full path and get rid of relative bits
                        $relPath = realpath(sprintf('%s/%s/%s', $sourceRoot, dirname($asset->getSourcePath()), dirname($args)));
                        if (!$relPath) {
                            if (!$factory->isDebug()) {
                                continue;
                            }

                            throw new InvalidArgumentException(sprintf('Traversing to non existent directory: asset=%s, %s', $asset->getSourcePath(), $args));
                        }
                        if (0 !== strpos($relPath, $sourceRoot)) {
                            if (!$factory->isDebug()) {
                                continue;
                            }

                            throw new InvalidArgumentException(sprintf('Traversing out of current source root: asset=%s, %s', $asset->getSourcePath(), $args));
                        }

                        // chop of absolute source root and tuck asset at the end again
                        $args = sprintf('%s/%s', substr($relPath, strlen($sourceRoot)), basename($args));
                    } else {
                        // .file!
                    }
                }

                // avoid doing too much (and loops!)
                $key = sprintf('%s:%s', $directive, $args);
                if (in_array($key, $this->processed)) {
                    continue;
                }
                $this->processed[] = $key;

                switch ($directive) {
                case 'require':
if ($factory->isDebug()) { echo ' require ('.$asset->getSourcePath().'): '.$args.PHP_EOL; }
                    $collection->add($sub = $factory->createAsset($args));
                    foreach ($asset->getFilters() as $filter) {
                        $sub->ensureFilter($filter);
                    }
                    break;

                case 'require_tree':
                    // tree always references a directory
if ($factory->isDebug()) { echo ' require_tree ('.$asset->getSourcePath().'): '.$args.PHP_EOL; }
                    $collection->add($factory->createAsset($args.'/*.*', $asset->getFilters());
                    $collection->add($factory->createAsset($args.'/*/*.*', $asset->getFilters());

                    break;

                case 'require_self':
                    $collection->add($asset);
                    break;
                }
            }
        }

        return $collection;
    }
}
