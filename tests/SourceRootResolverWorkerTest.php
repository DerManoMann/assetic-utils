<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
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

        $asset = $factory->createAsset('sub1/js/standalone.js');
        $asset = $this->expectSingleAsset($asset);
        $this->assertTrue($asset instanceof FileAsset);
        $this->assertEquals($defaultRoot, $asset->getSourceRoot());
        $this->assertEquals('sub1/js/standalone.js', $asset->getSourcePath());
    }

    /**
     */
    public function testFile()
    {
        $factory = $this->getFactory($defaultRoot = null, [new SourceRootResolverWorker(['/tmp', __DIR__.'/assets'])]);
        $expectedRoot = __DIR__.'/assets';

        $asset = $factory->createAsset('sub1/js/standalone.js');
        $asset = $this->expectSingleAsset($asset);
        $this->assertTrue($asset instanceof FileAsset);
        $this->assertEquals($expectedRoot, $asset->getSourceRoot());
        $this->assertEquals('sub1/js/standalone.js', $asset->getSourcePath());
    }

    /**
     */
    public function testGlob()
    {
        $factory = $this->getFactory($defaultRoot = null, [new SourceRootResolverWorker(['/tmp', __DIR__.'/assets'])]);
        $expectedRoot = __DIR__.'/assets';

        $asset = $factory->createAsset('sub1/js/require_*.js');
        $asset = $this->expectSingleAsset($asset);
        $this->assertTrue($asset instanceof GlobAsset);
        $this->assertEquals($expectedRoot, $asset->getSourceRoot());

        $this->assertAssetSources(['sub1/js/require_multi.js', 'sub1/js/require_self.js', 'sub1/js/require_tree.js', 'sub1/js/require_wildcard.js'], $asset);
    }
}
