<?php

namespace Phpsog\Provider\Asset;

use Phpsog\Exporter;
use SplFileInfo;
use Kaloa\Filesystem\PathHelper;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Provider
{
    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * @var Exporter
     */
    protected $exporter;

    /**
     *
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @param array      $config
     * @param Exporter   $exporter
     * @param PathHelper $pathHelper
     */
    public function __construct(array $config, Exporter $exporter,
            PathHelper $pathHelper)
    {
        $this->config     = $config;
        $this->exporter   = $exporter;
        $this->pathHelper = $pathHelper;
    }

    /**
     *
     * @param SplFileInfo $file
     */
    public function compile(SplFileInfo $file)
    {
        $file   = $file->getPathname();
        $config = $this->config;

        $relativePath = substr($file, strlen($config['project.dir']
                      . '/' . $config['resources.dir'] . '/'));

        $exportPath = $config['project.dir'] . '/' . $config['export.dir']
                    . '/' . pathinfo($relativePath, PATHINFO_DIRNAME)
                    . '/' . basename($relativePath);

        $content = file_get_contents($file);

        $this->exporter->export($exportPath, $content);
    }
}
