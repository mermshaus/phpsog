<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Phpsog\ExportEvent;

class Exporter
{
    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @var EventDispatcherInterface
     */
    protected $logger;

    /**
     *
     * @param PathHelper $pathHelper
     */
    public function __construct(PathHelper $pathHelper,
            EventDispatcherInterface $logger)
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
        $this->logger->dispatch('onMkdir', new ExportEvent('[mkdir]  ' . $normPath));
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

        file_put_contents($exportPath, $content);

        $this->logger->dispatch('onExport', new ExportEvent('[export] ' . $normPath));
    }
}
