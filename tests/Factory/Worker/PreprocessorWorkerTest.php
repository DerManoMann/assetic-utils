<?php

namespace Radebatz\Assetic\Tests\Factory\Worker;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\AssetWriter;
use Radebatz\Assetic\Tests\AsseticTestCase;
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
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [new PreprocessorWorker($this->getAssetPreprocessor())]);

        $asset = $factory->createAsset('core/js/require.js');
        $this->assertTrue($asset instanceof AssetCollection);
        $this->assertAssetSources(['core/js/standalone.js', 'core/js/require.js'], $asset);
    }

    /**
     */
    public function testWriteBinaryFile()
    {
        $resource = 'core/img/bruce.png';
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath(), [new PreprocessorWorker($this->getAssetPreprocessor())]);

        $asset = $factory->createAsset($resource);
        $asset = $this->expectSingleAsset($asset);
        $this->assertTrue($asset instanceof FileAsset);
        $this->assertEquals($defaultRoot, $asset->getSourceRoot());
        $this->assertEquals($resource, $asset->getSourcePath());
        $asset->setTargetPath('bruce.png');

        $tmpdir = tempnam(sys_get_temp_dir(), 'ass_');
        unlink($tmpdir);
        mkdir($tmpdir);
        $writer = new AssetWriter($tmpdir);
        $writer->writeAsset($asset);
        $this->assertEquals(md5_file($defaultRoot.'/'.$resource), md5_file($tmpdir.'/bruce.png'));
    }
}
