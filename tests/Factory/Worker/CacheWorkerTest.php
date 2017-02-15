<?php

namespace Radebatz\Assetic\Tests\Factory\Worker;

use Assetic\Asset\AssetCollection;
use Assetic\Cache\FilesystemCache;
use Radebatz\Assetic\Tests\AsseticTestCase;
use Radebatz\Assetic\Factory\Worker\CacheWorker;

/**
 * Test CacheWorker.
 */
class CacheWorkerTest extends AsseticTestCase
{

    /**
     */
    public function testBasic()
    {
        $cacheDir = $this->getCachePath();
        @mkdir($cacheDir, 0777);
        $cacheWorker = new CacheWorker($cache = new FilesystemCache($cacheDir));

        // make cache empty
        $cacheFiles = glob($cacheDir.'/*');
        foreach ($cacheFiles as $file) {
            $cache->remove(basename($file));
        }

        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [$cacheWorker]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        // no preprocessor...
        $this->assertAssetSources(['core/js/require.js'], $asset);

        // force caching it...
        $asset->load();

        // check cache is not empty
        $cacheFiles = glob($cacheDir.'/*');
        $this->assertEquals(1, count ($cacheFiles));

        $this->assertEquals(file_get_contents(array_pop($cacheFiles)), file_get_contents($defaultRoot.'/core/js/require.js'));
    }
}
