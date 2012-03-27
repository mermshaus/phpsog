<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;

class Exporter
{
    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @param PathHelper $pathHelper
     */
    public function __construct(PathHelper $pathHelper)
    {
        $this->pathHelper = $pathHelper;
    }

    /**
     *
     * @param string $exportPath
     * @param string $content
     */
    public function export($exportPath, $content)
    {
        $ph = $this->pathHelper;

        if (!file_exists(dirname($exportPath))) {
            mkdir(dirname($exportPath), 0777, true);
            echo '  [mkdir] ' . $ph->normalize(dirname($exportPath)) . PHP_EOL;
        }

        $normPath = $ph->normalize($exportPath);

        if (strpos($normPath, getcwd()) === 0) {
            $normPath = substr($normPath, strlen(getcwd()));
            $normPath = ltrim($normPath, '/');
        }

        echo '  [export] ' . $normPath . PHP_EOL;

        file_put_contents($exportPath, $content);
    }
}
