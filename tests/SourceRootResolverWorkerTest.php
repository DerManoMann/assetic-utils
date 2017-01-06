<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Radebatz\Assetic\Factory\Worker\PreprocessorWorker;
use Radebatz\Assetic\Factory\Worker\SourceRootResolverWorker;

/**
 * Test SourceRootResolverWorker.
 */
class SourceRootResolverWorkerTest extends AsseticTestCase
{
    /**
     */
    public function testDefaultRoot()
    {
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets');

        $asset = $factory->createAsset('core/js/standalone.js');
        $asset = $this->expectSingleAsset($asset);
        $this->assertTrue($asset instanceof FileAsset);
        $this->assertEquals($defaultRoot, $asset->getSourceRoot());
        $this->assertEquals('core/js/standalone.js', $asset->getSourcePath());
    }

    /**
     */
    public function testFile()
    {
        $factory = $this->getFactory($defaultRoot = null, [new SourceRootResolverWorker(['/tmp', __DIR__.'/assets'])]);
        $expectedRoot = __DIR__.'/assets';

        $asset = $factory->createAsset('core/js/standalone.js');
        // double nesting as the resolver uses $factory->createAsset
        $asset = $this->expectSingleAsset($this->expectSingleAsset($asset));
        $this->assertTrue($asset instanceof FileAsset);
        $this->assertEquals($expectedRoot, $asset->getSourceRoot());
        $this->assertEquals('core/js/standalone.js', $asset->getSourcePath());
    }

    /**
     */
    public function testGlob()
    {
        $factory = $this->getFactory($defaultRoot = null, [new SourceRootResolverWorker(['/tmp', __DIR__.'/assets'])]);
        $expectedRoot = __DIR__.'/assets';

        $asset = $factory->createAsset('core/js/require_*.js');
        // double nesting as the resolver uses $factory->createAsset
        $asset = $this->expectSingleAsset($this->expectSingleAsset($asset));
        $this->assertTrue($asset instanceof GlobAsset);
        $this->assertEquals($expectedRoot, $asset->getSourceRoot());

        $this->assertAssetSources(['core/js/require_multi.js', 'core/js/require_self.js', 'core/js/require_tree.js', 'core/js/require_wildcard.js'], $asset);
    }

    /**
     */
    public function testComplex()
    {
        $assetsBase = __DIR__.'/assets';
        $factory = $this->getFactory(
            null, [
                new SourceRootResolverWorker([
                    $assetsBase.'/core',
                    $assetsBase.'/other',
                ]),
                new SourceRootResolverWorker([
                    $assetsBase.'/plugins',
                ]),
                new PreprocessorWorker($this->getAssetPreprocessor())
            ]
        );

        $asset = $factory->createAsset('fancy/css/fancy_red.css');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['fancy/css/fancy_base.css', 'fancy/css/fancy_red.css'], $asset);
    }
}
