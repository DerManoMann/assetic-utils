<?php

namespace Radebatz\Assetic\Tests;

use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;
use Assetic\Cache\CacheInterface;
use Radebatz\Assetic\Cache\Psr6Cache;

/**
 * Test Psr6Cache.
 */
class Psr6CacheTest extends AsseticTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (!class_exists('Radebatz\ACache\Decorators\Psr\CacheItemPool')) {
            $this->markTestSkipped('Need PSR-6 implementation for these tests');
        }
    }

    /**
     */
    public function testBasic()
    {
        $cache = new Psr6Cache(new CacheItemPool(new ArrayCache()));

        $cache->set('foo', 'bar');
        $this->assertFalse($cache->has('xx'));
        $this->assertTrue($cache->has('foo'));
        $this->assertEquals('bar', $cache->get('foo'));
    }
}
