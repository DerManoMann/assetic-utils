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

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['core/js/standalone.js', 'core/js/require.js'], $asset);
    }
}
