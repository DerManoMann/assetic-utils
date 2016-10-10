<?php

namespace Radebatz\Assetic\Factory\Worker;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;
use Radebatz\Assetic\AssetPreprocessor;

/**
 * Asset preprocessor worker.
 */
class PreprocessorWorker implements WorkerInterface
{
    protected $preprocessor;

    /**
     */
    public function __construct(AssetPreprocessor $preprocessor) {
        $this->preprocessor = $preprocessor;
    }

    /**
     * Process.
     */
    public function process(AssetInterface $asset, AssetFactory $factory) {
        return $this->preprocessor->process($asset, $factory);
    }
}
