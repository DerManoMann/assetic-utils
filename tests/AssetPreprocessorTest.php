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
        $resource = 'core/js/standalone.js';
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath());
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
        $resource = 'core/js/require.js';
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath());
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);
        $this->assertAssetSources(['core/js/standalone.js', 'core/js/require.js'], $result);
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireSelf($factory)
    {
        $resource = 'core/js/require_self.js';
        $defaultRoot = $this->getAssetsPath();
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);
        $this->assertAssetSources(['core/js/require_self.js'], $result);
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireMulti($factory)
    {
        $resource = 'core/js/require_multi.js';
        $defaultRoot = $this->getAssetsPath();
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['core/js/standalone.js', 'core/js/require_multi.js', 'core/js/static.js'], $result);

        $expectedContent = implode("\n\n", ["var standalone = 'standalone';", "var multi = 'multi';", "var name = 'static';"]) . "\n";
        $this->assertEquals($expectedContent, $result->dump());
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireTree($factory)
    {
        $resource = 'core/js/require_tree.js';
        $defaultRoot = $this->getAssetsPath();
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['other/js/tree/sub2tree21.js', 'other/js/tree/sub2tree22.js', 'core/js/require_tree.js'], $result);
        // ensure everything loads
        $result->dump();
    }

    /**
     * @dataProvider testFactories
     */
    public function testRequireWildcard($factory)
    {
        $resource = 'core/js/require_wildcard.js';
        $defaultRoot = $this->getAssetsPath();
        $asset = new FileAsset(sprintf('%s/%s', $defaultRoot, $resource), [], $defaultRoot, $resource, []);

        $preprocessor = $this->getAssetPreprocessor();

        $result = $preprocessor->process($asset, $factory);
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof AssetCollection);

        $this->assertAssetSources(['core/js/standalone.js', 'core/js/static.js', 'core/js/require_wildcard.js'], $result);

        // ensure everything loads
        $result->dump();
    }
}
