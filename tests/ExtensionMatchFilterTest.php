<?php

namespace Radebatz\Assetic\Tests;

use Assetic\Asset\FileAsset;
use Assetic\Filter\CallablesFilter;
use Radebatz\Assetic\Filter\ExtensionMatchFilter;

/**
 * Test ExtensionMatchFilter.
 */
class ExtensionMatchFilterTest extends AsseticTestCase
{
    /**
     */
    public function testMatch()
    {
        $dumperCalled = false;
        $jsMatchFilter = new ExtensionMatchFilter(new CallablesFilter(null, function ($asset) use (&$dumperCalled) { $dumperCalled = true; }), 'js');

        // create asset
        $factory = $this->getFactory($defaultRoot = __DIR__.'/assets', [], ['jsmatch' => $jsMatchFilter]);
        $asset = $factory->createAsset('sub1/js/standalone.js', ['jsmatch']);
        $asset->dump();
        $this->assertTrue($dumperCalled);
    }
}
