<?php

namespace Radebatz\Assetic\Util;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Custom filesystem.
 */
class Filesystem extends SymfonyFilesystem
{

    /**
     * {@inheritdoc}
     */
    public function mkdir($dirs, $mode = 0777)
    {
        // resolve missing parent dirs so we can apply $mode to each and every one
        $resolved = [];
        foreach ($this->toIterator($dirs) as $dir) {
            $resolved[] = $dir;
            while (!is_dir(dirname($dir))) {
                $dir = dirname($dir);
                if (in_array($dir, ['/', '.', '..'])) {
                    break;
                }
                $resolved[] = $dir;
            }
        }
        $resolved = array_reverse($resolved);

        foreach ($resolved as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            if (true !== @mkdir($dir, $mode, true)) {
                $error = error_get_last();
                if (!is_dir($dir)) {
                    // The directory was not created by a concurrent process. Let's throw an exception with a developer friendly error message if we have one
                    if ($error) {
                        throw new IOException(sprintf('Failed to create "%s": %s.', $dir, $error['message']), 0, null, $dir);
                    }
                    throw new IOException(sprintf('Failed to create "%s"', $dir), 0, null, $dir);
                }
            }
            chmod($dir, $mode);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function toIterator($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(is_array($files) ? $files : array($files));
        }

        return $files;
    }
}
