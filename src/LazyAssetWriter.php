<?php

namespace Radebatz\Assetic;

use Symfony\Component\Filesystem\Filesystem;
use Assetic\AssetWriter;
use Assetic\Asset\AssetInterface;
use Assetic\Util\VarUtils;
use Radebatz\Assetic\Util\WebUtils;

/**
 * Lazy writer that will only write if the file doesn't exist.
 */
class LazyAssetWriter extends AssetWriter
{
    protected $dir;
    protected $values;

    /**
     * Constructor.
     *
     * @param string $dir    The base web directory
     * @param array  $values Variable values
     *
     * @throws \InvalidArgumentException if a variable value is not a string
     */
    public function __construct($dir, array $values = array())
    {
        parent::__construct($dir, $values);
        $this->dir = $dir;
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function writeAsset(AssetInterface $asset)
    {
        // make targetPath neutral
        $asset->setTargetPath('');

        $filesystem = new Filesystem();
        foreach (VarUtils::getCombinations($asset->getVars(), $this->values) as $combination) {
            $asset->setValues($combination);

            $absoluteAssetPath = $this->dir.'/'.WebUtils::getVersionTargetPath($asset);

            if (!file_exists($absoluteAssetPath)) {
                $filesystem->mkdir(dirname($absoluteAssetPath));
                static::write($absoluteAssetPath, $asset->dump());
            }
        }
    }
}
