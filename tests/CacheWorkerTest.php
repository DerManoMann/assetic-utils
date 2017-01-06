<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\AssetCollection;
use Assetic\Cache\ArrayCache;
use Radebatz\Assetic\Factory\Worker\CacheWorker;
use Radebatz\Assetic\Factory\Worker\PreprocessorWorker;

/**
 * Test CacheWorker.
 */
class CacheWorkerTest extends AsseticTestCase
{

    /**
     */
    public function testBasic()
    {
        $cacheWorker = new CacheWorker($cache = new ArrayCache());
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets', [$cacheWorker]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        // no preprocessor...
        $this->assertAssetSources(['core/js/require.js'], $asset);

        // force caching it...
        $asset->load();

        // check cache is not empty
        $rc = new \ReflectionClass($cache);
        $rp = $rc->getProperty('cache');
        $rp->setAccessible(true);
        $this->assertEquals(1, count($rp->getValue($cache)));
    }
}
