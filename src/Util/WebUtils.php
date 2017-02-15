<?php

namespace Radebatz\Assetic\Util;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;
use Assetic\Util\VarUtils;

/**
 * Collection of web utils.
 */
class WebUtils
{

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
        $path = VarUtils::resolve($asset->getTargetPath(), $asset->getVars(), $asset->getValues());

        // merge version into path
        $version = self::getAssetVersion($asset, $additionalFilter, $salt);
        $info = pathinfo($path);

        $path = $info['dirname'] . DIRECTORY_SEPARATOR . $info['basename'] . '.' . $version;
        if ($info['extension']) {
            $path .= '.' . $info['extension'];
        }

        return $path;
    }

    /**
     * Returns a version key for the given asset.
     *
     * The key is composed of everything but an asset's content:
     *
     *  * source root
     *  * source path
     *  * target url
     *  * last modified
     *  * filters
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
        $cacheKey .= $asset->getLastModified();

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
