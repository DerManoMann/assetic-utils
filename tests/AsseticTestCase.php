<?php

namespace Radebatz\Assetic\Tests;

use Symfony\Component\Yaml\Parser;
use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\FilterManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Radebatz\Assetic\AssetPreprocessor;
use Radebatz\Assetic\Factory\Worker\SourceRootResolverWorker;

/**
 * Base test case.
 */
class AsseticTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Get the assets base path.
     */
    protected function getAssetsPath()
    {
        return __DIR__.'/Resources/assets';
    }

    /**
     * Get the writer base path.
     */
    protected function getWriterPath()
    {
        return __DIR__.'/Resources/writer';
    }

    /**
     * Get the file cache path.
     */
    protected function getCachePath()
    {
        return __DIR__.'/Resources/cache';
    }

    /**
     * Get default config.
     */
    protected function getDefaultConfig()
    {
        $parser = new Parser();
        return $parser->parse(file_get_contents(__DIR__.'/test_config.yml'));
    }

    /**
     * Get a basic factory.
     */
    protected function getFactory($root = false, array $workers = [], array $filters = [])
    {
        $defaultConfig = $this->getDefaultConfig();

        $root = false !== $root ? $root : $this->getAssetsPath();
        $factory = new AssetFactory($root, $defaultConfig['assetic']['debug']);
        $factory->setAssetManager(new LazyAssetManager($factory));

        $filterManager = new FilterManager();
        foreach ($filters as $name => $filter) {
            $filterManager->set($name, $filter);
        }

        $factory->setFilterManager($filterManager);

        foreach ($workers as $worker) {
            $factory->addWorker($worker);
        }

        return $factory;
    }

    /**
     * Factory provider.
     */
    public function testFactories()
    {
        return [
            'default' => [$this->getFactory($this->getAssetsPath())],
            'resolving' => [$this->getFactory(null, [new SourceRootResolverWorker(['/tmp', $this->getAssetsPath()])])],
        ];
    }

    /**
     * Get an asset preprocessor.
     */
    protected function getAssetPreprocessor(array $config = [])
    {
        $defaultConfig = $this->getDefaultConfig();
        $preprocessor = new AssetPreprocessor(array_merge($defaultConfig['assetic']['preprocessor'], $config));

        return $preprocessor;
    }

    /**
     * Assert a particular order of sources.
     */
    public function assertAssetSources($expected, AssetCollectionInterface $assetCollection)
    {
        $sources = [];
        foreach ($assetCollection as $asset) {
            $sources[] = $asset->getSourcePath();
        }

        $this->assertEquals($expected, $sources);
    }

    /**
     * Expect a single asset from a collection and return it.
     */
    public function expectSingleAsset(AssetCollectionInterface $assetCollection)
    {
        $asset = null;
        foreach ($assetCollection->all() as $leaf) {
            if ($asset) {
                $this->fail('Found more than one asset in collection');
            }
            $asset = $leaf;
        }

        return $asset;
    }

    /**
     * Debug asset.
     */
    protected function dumpAsset($asset, $content = false, $level = 0)
    {
        if ($asset instanceof AssetCollectionInterface) {
            $left = str_pad('', $level * 2);
            echo PHP_EOL;
            echo $left.get_class($asset).PHP_EOL;
            echo $left.'TargetPath: '.$asset->getTargetPath().PHP_EOL;
            echo $left.'LastModified: '.$asset->getLastModified().PHP_EOL;
            foreach ($asset->all() as $ass) {
                $this->dumpAsset($ass, $content, $level + 1);
            }
        } else if ($asset) {
            $left = str_pad('', $level * 2);
            echo PHP_EOL;
            echo $left.get_class($asset).PHP_EOL;
            echo $left.'SourceRoot: '.$asset->getSourceRoot().PHP_EOL;
            echo $left.'SourcePath: '.$asset->getSourcePath().PHP_EOL;
            echo $left.'SourceDirectory: '.$asset->getSourceDirectory().PHP_EOL;
            echo $left.'TargetPath: '.$asset->getTargetPath().PHP_EOL;
            echo $left.'LastModified: '.$asset->getLastModified().PHP_EOL;
            if ($content) {
                echo PHP_EOL;
                var_dump($asset->dump());
            }
        } else {
            var_dump($asset);
        }
    }
}
