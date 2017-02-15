<?php

namespace Radebatz\Assetic\Tests\Util;

use Assetic\Asset\FileAsset;
use Radebatz\Assetic\Tests\AsseticTestCase;
use Radebatz\Assetic\Util\WebUtils;

/**
 * Test WebUtils.
 */
class WebUtilsTest extends AsseticTestCase
{
    /**
     */
    public function testVersionTargetPath()
    {
        // create asset
        $factory = $this->getFactory($defaultRoot = $this->getAssetsPath());
        $asset = $factory->createAsset('core/js/standalone.js');
        $this->expectSingleAsset($asset);

        // is collection
        $leafs = $asset->all();
        $asset = array_pop($leafs);
        // double check
        $this->assertTrue($asset instanceof FileAsset);

        $targetPath = WebUtils::getVersionTargetPath($asset);

        $this->assertTrue((bool) preg_match('#core/js/standalone\.[0-9a-z]+\.js#', $targetPath));
    }
}
