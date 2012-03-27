<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use Phpsog\Logger;

class Exporter
{
    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     *
     * @param PathHelper $pathHelper
     */
    public function __construct(PathHelper $pathHelper, Logger $logger)
    {
        $this->pathHelper = $pathHelper;
        $this->logger     = $logger;
    }

    public function mkdir($path, $rights = 0777, $recursive = true)
    {
        $normPath = $this->pathHelper->normalize($path);

        if (strpos($normPath, getcwd()) === 0) {
            $normPath = substr($normPath, strlen(getcwd()));
            $normPath = ltrim($normPath, '/');
        }

        mkdir($path, $rights, $recursive);
        $this->logger->log('  [mkdir]  ' . $normPath);
    }

    /**
     *
     * @param string $exportPath
     * @param string $content
     */
    public function export($exportPath, $content)
    {
        if (!file_exists(dirname($exportPath))) {
            $this->mkdir(dirname($exportPath));
        }

        $normPath = $this->pathHelper->normalize($exportPath);

        if (strpos($normPath, getcwd()) === 0) {
            $normPath = substr($normPath, strlen(getcwd()));
            $normPath = ltrim($normPath, '/');
        }

        $this->logger->log('  [export] ' . $normPath);

        file_put_contents($exportPath, $content);
    }
}
