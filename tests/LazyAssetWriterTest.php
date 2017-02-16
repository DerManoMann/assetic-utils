<?php

namespace Radebatz\Assetic\Tests;

use Radebatz\Assetic\LazyAssetWriter;

/**
 * Test LazyAssetWriter.
 */
class LazyAssetWriterTest extends AsseticTestCase
{
    /**
     */
    public function testSubdir()
    {
        $resource = 'core/js/standalone.js';
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath());
        $asset = $factory->createAsset($resource);
        $asset = $this->expectSingleAsset($asset);

        $assetWriter = new LazyAssetWriter($this->getWriterPath());
        $assetWriter->writeAsset($asset);

        // TODO: validate
    }
}
