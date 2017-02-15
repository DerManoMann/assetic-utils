<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\AssetCollection;
use Assetic\Cache\FilesystemCache;
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
        $cacheDir = __DIR__.'/cache';
        @mkdir($cacheDir, 0777);
        $cacheWorker = new CacheWorker($cache = new FilesystemCache($cacheDir));
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets', [$cacheWorker]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        // no preprocessor...
        $this->assertAssetSources(['core/js/require.js'], $asset);

        // force caching it...
        $asset->load();

        // check cache is not empty
        $cacheFiles = glob($cacheDir.'/*');
        $this->assertEquals(1, count ($cacheFiles));

        $this->assertEquals(file_get_contents(array_pop($cacheFiles)), file_get_contents(__DIR__.'/assets/core/js/require.js'));
    }
}
