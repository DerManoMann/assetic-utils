<?php

namespace Radebatz\Assetic\Tests\Factory\Worker;

use Assetic\Asset\AssetCollection;
use Assetic\Cache\FilesystemCache;
use Radebatz\Assetic\Tests\AsseticTestCase;
use Radebatz\Assetic\Factory\Worker\VersioningWorker;

/**
 * Test VersioningWorker.
 */
class VersioningWorkerTest extends AsseticTestCase
{

    /**
     */
    public function testDefaults()
    {
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [new VersioningWorker()]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['core/js/require.js'], $asset);
        $this->assertTrue(0 === strpos($asset->getTargetPath(), 'core/js/require.'));

        // force caching it...
        $asset->load();
    }

    /**
     */
    public function testPrefix()
    {
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [new VersioningWorker('foo/')]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['core/js/require.js'], $asset);
        $this->assertTrue(0 === strpos($asset->getTargetPath(), 'foo/core/js/require.'));

        // force caching it...
        $asset->load();
    }
}
