<?php

namespace Radebatz\Assetic\Tests\Factory\Worker;

use Assetic\Asset\AssetCollection;
use Assetic\Cache\FilesystemCache;
use Radebatz\Assetic\Tests\AsseticTestCase;
use Radebatz\Assetic\Factory\Worker\WebCacheWorker;

/**
 * Test WebCacheWorker.
 */
class WebCacheWorkerTest extends AsseticTestCase
{

    /**
     */
    public function testBasic()
    {
        $webCacheWorker = new WebCacheWorker();
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [$webCacheWorker]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        // no preprocessor...
        $this->assertAssetSources(['core/js/require.js'], $asset);

        // force caching it...
        $asset->load();

        $this->dumpAsset($asset);
    }
}
