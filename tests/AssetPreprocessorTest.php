<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use Radebatz\Assetic\AssetPreprocessor;

/**
 * Test AssetPreprocessor.
 */
class AssetPreprocessorTest extends AsseticTestCase
{
    /**
     */
    public function testNoStatements()
    {
        $resource = 'sub1/js/standalone.js';
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets');
        $asset = $factory->createAsset($resource);
        $asset = $this->expectSingleAsset($asset);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNull($result);
    }

    /**
     */
    public function testRequireSingle()
    {
        $resource = 'sub1/js/require.js';
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets');
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);
        $this->assertAssetSources(['sub1/js/standalone.js', 'sub1/js/require.js'], $result);
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireSelf($factory)
    {
        $resource = 'sub1/js/require_self.js';
        $defaultRoot = __DIR__.'/assets';
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);
        $this->assertAssetSources(['sub1/js/require_self.js'], $result);
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireMulti($factory)
    {
        $resource = 'sub1/js/require_multi.js';
        $defaultRoot = __DIR__.'/assets';
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['sub1/js/standalone.js', 'sub1/js/require_multi.js', 'sub1/js/static.js'], $result);

        $expectedContent = implode("\n\n", ["var standalone = 'standalone';", "var multi = 'multi';", "var name = 'static';"]) . "\n";
        $this->assertEquals($expectedContent, $result->dump());
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireTree($factory)
    {
        $resource = 'sub1/js/require_tree.js';
        $defaultRoot = __DIR__.'/assets';
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['sub2/js/tree/sub2tree21.js', 'sub2/js/tree/sub2tree22.js', 'sub1/js/require_tree.js'], $result);
        // ensure everything loads
        $result->dump();
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireWildcard($factory)
    {
        $resource = 'sub1/js/require_wildcard.js';
        $defaultRoot = __DIR__.'/assets';
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['sub1/js/standalone.js', 'sub1/js/static.js', 'sub1/js/require_wildcard.js'], $result);

        // ensure everything loads
        $result->dump();
    }
}
