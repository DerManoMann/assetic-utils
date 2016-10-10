<?php

namespace Radebatz\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * Conditional filter wrapper based on file extension.
 */
class ExtensionMatchFilter implements FilterInterface {
    protected $filter;
    protected $extension;
    protected $debug;

    /**
     */
    public function __construct(FilterInterface $filter, $extension, $debug = false) {
        $this->filter = $filter;
        $this->extension = $extension;
        $this->debug = $debug;
    }

    /**
     */
    protected function isMatch(AssetInterface $asset) {
        return pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION) == $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function filterLoad(AssetInterface $asset) {
        if ($this->isMatch($asset)) {
            $this->filter->filterLoad($asset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filterDump(AssetInterface $asset) {
        if ($this->isMatch($asset)) {
            $this->filter->filterDump($asset);
        }
    }
}
