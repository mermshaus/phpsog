<?php

namespace org\ermshaus\filesystem;

class PathHelper
{
    public function normalizeDirectorySeparators($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('$path must be of type string');
        }

        $directorySeparatorInverse = (DIRECTORY_SEPARATOR === '/') ? '\\' : '/';

        return str_replace($directorySeparatorInverse, DIRECTORY_SEPARATOR,
                $path);
    }

    /**
     *
     * @param string $path
     * @return string
     * @throws \InvalidArgumentException
     */
    public function normalize($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('$path must be of type string');
        }

        $path = trim($path);

        $path = $this->normalizeDirectorySeparators($path);

        $isAbsolutePath = (substr($path, 0, 1) === '/');

        $components = explode('/', $path);

        $newComponents = array();

        foreach ($components as $component) {
            switch ($component) {
                case '':
                case '.':
                    // Discard
                    break;
                case '..':
                    $c = count($newComponents);
                    if (
                        ($c === 0 && !$isAbsolutePath)
                        || ($c > 0 && $newComponents[$c - 1] === '..')
                    ) {
                        // Relative paths may start with ".." components
                        $newComponents[] = $component;
                    } else {
                        array_pop($newComponents);
                    }
                    break;
                default:
                    $newComponents[] = $component;
                    break;
            }
        }

        $newPath = ($isAbsolutePath ? '/' : '') . implode('/', $newComponents);

        if ($newPath === '') {
            $newPath = '.';
        }

        return $newPath;
    }
}
