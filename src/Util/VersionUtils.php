<?php

namespace Radebatz\Assetic\Util;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;
use Assetic\Util\VarUtils;

/**
 * Collection of version related utilities.
 */
class VersionUtils
{

    /**
     * Set a target path based on the original asset target path and asset version key.
     *
     * @param AssetInterface  $asset            The asset
     * @param FilterInterface $additionalFilter Any additional filter being applied
     * @param string          $salt             Salt for the key
     *
     * @return string A versioned target path
     */
    public static function setVersionTargetPath(AssetInterface $asset, FilterInterface $additionalFilter = null, $salt = '')
    {
        if ($asset instanceof FileAsset) {
            $asset->setTargetPath('');
            $asset->setTargetPath(static::getVersionTargetPath($asset));
        } elseif ($asset instanceof AssetCollectionInterface) {
            // use first to set target path
            $leafs = $asset->all();
            if ($leafs) {
                $asset->setTargetPath('');
                $asset->setTargetPath(static::getVersionTargetPath($leafs[0]));
            }
        }
    }

    /**
     * Get a target path based on the original asset target path and asset version key.
     *
     * @param AssetInterface  $asset            The asset
     * @param FilterInterface $additionalFilter Any additional filter being applied
     * @param string          $salt             Salt for the key
     *
     * @return string A versioned target path
     */
    public static function getVersionTargetPath(AssetInterface $asset, FilterInterface $additionalFilter = null, $salt = '')
    {
        $path = VarUtils::resolve($asset->getSourcePath(), $asset->getVars(), $asset->getValues());

        // merge version into path
        $version = self::getAssetVersion($asset, $additionalFilter, $salt);
        $info = pathinfo($path);

        $path = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $version;
        if ($info['extension']) {
            $path .= '.' . $info['extension'];
        }

        return $path;
    }

    /**
     * Returns a version key for the given asset.
     *
     * The key is composed of everything but an asset's last modified timestamp:
     *
     *  * source root
     *  * source path
     *  * target url
     *  * filters
     *  * content
     *
     * @param AssetInterface  $asset            The asset
     * @param FilterInterface $additionalFilter Any additional filter being applied
     * @param string          $salt             Salt for the key
     *
     * @return string A version key for this asset
     */
    public static function getAssetVersion(AssetInterface $asset, FilterInterface $additionalFilter = null, $salt = '')
    {
        if ($additionalFilter) {
            $asset = clone $this;
            $asset->ensureFilter($additionalFilter);
        }

        $cacheKey  = $asset->getSourceRoot();
        $cacheKey .= $asset->getSourcePath();
        $cacheKey .= $asset->getTargetPath();
        $cacheKey .= $asset->dump();

        foreach ($asset->getFilters() as $filter) {
            if ($filter instanceof HashableInterface) {
                $cacheKey .= $filter->hash();
            } else {
                $cacheKey .= serialize($filter);
            }
        }

        if ($values = $asset->getValues()) {
            asort($values);
            $cacheKey .= serialize($values);
        }

        return md5($cacheKey.$salt);
    }
}
