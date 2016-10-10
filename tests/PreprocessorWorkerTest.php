<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\AssetCollection;
use Radebatz\Assetic\Factory\Worker\PreprocessorWorker;

/**
 * Test PreprocessorWorker.
 */
class PreprocessorWorkerTest extends AsseticTestCase
{

    /**
     */
    public function testRequire()
    {
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets', [new PreprocessorWorker($this->getAssetPreprocessor())]);

        $asset = $factory->createAsset('sub1/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['sub1/js/standalone.js', 'sub1/js/require.js'], $asset);
    }
}
