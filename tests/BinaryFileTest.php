<?php

namespace Radebatz\Assetic\Tests;

use Assetic\AssetWriter;
use Assetic\Asset\FileAsset;
use Radebatz\Assetic\Factory\Worker\SourceRootResolverWorker;
use Radebatz\Assetic\Factory\Worker\PreprocessorWorker;

/**
 * Test binary asset.
 */
class BinaryFileTest extends AsseticTestCase
{
    /**
     */
    public function testWrite()
    {
        $resource = 'sub1/img/bruce.png';
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets', [new PreprocessorWorker($this->getAssetPreprocessor())]);

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
        $this->assertEquals(md5_file(__DIR__.'/assets/'.$resource), md5_file($tmpdir.'/bruce.png'));
    }
}
