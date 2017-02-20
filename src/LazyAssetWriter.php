<?php

namespace Radebatz\Assetic;

use Assetic\AssetWriter;
use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Util\VarUtils;
use Radebatz\Assetic\Util\VersionUtils;
use Radebatz\Assetic\Util\Filesystem;

/**
 * Lazy writer that will only write if the file doesn't exist, using a versioned target path.
 *
 * Writes both collections and individual assets.
 */
class LazyAssetWriter extends AssetWriter
{
    protected $dir;
    protected $values;
    protected $recursive;
    protected $callback;

    /**
     * Constructor.
     *
     * @param string    $dir       The base web directory
     * @param array     $values    Variable values
     * @param bool      $recursive If set write collection and it's leafs.
     * @param callable  $callback  Optional call for path preprocessing.
     *
     * @throws \InvalidArgumentException if a variable value is not a string
     */
    public function __construct($dir, array $values = array(), $recursive = true, $callback = null)
    {
        parent::__construct($dir, $values);
        $this->dir = $dir;
        $this->values = $values;
        $this->recursive = $recursive;
        $this->callback = $callback ?: function ($asset) { VersionUtils::setVersionTargetPath($asset); };
        if (!is_callable($this->callback)) {
            throw new InvalidArgumentException('Need a callable');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeAsset(AssetInterface $asset)
    {
        if ($asset instanceof AssetCollectionInterface && $this->recursive) {
            foreach ($asset->all() as $leaf) {
                $this->writeAsset($leaf);
            }
        }

        $this->writeLazy($asset);
    }

    protected function writeLazy(AssetInterface $asset)
    {
        // make targetPath neutral
        $asset->setTargetPath('');

        $filesystem = new Filesystem();
        foreach (VarUtils::getCombinations($asset->getVars(), $this->values) as $combination) {
            $asset->setValues($combination);
            call_user_func($this->callback, $asset);
            $absoluteAssetPath = $this->dir.'/'.$asset->getTargetPath();

            if (!file_exists($absoluteAssetPath)) {
                $filesystem->mkdir(dirname($absoluteAssetPath), 0777);
                static::write($absoluteAssetPath, $asset->dump());
                $filesystem->chmod($absoluteAssetPath, 0666);
            }
        }
    }
}
