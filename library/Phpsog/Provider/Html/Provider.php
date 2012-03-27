<?php

namespace Phpsog\Provider\Html;

use Phpsog\Exporter;
use SplFileInfo;
use Kaloa\Filesystem\PathHelper;

use Phpsog\Provider\Html\View;

/**
 *
 */
class Provider
{
    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $layout;

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
    public function __construct(array $config, Exporter $exporter, PathHelper $pathHelper)
    {
        $this->config     = $config;
        $this->exporter   = $exporter;
        $this->pathHelper = $pathHelper;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     *
     * @param  string $layoutFile
     * @param  array  $vars
     * @return string
     */
    protected function fillLayout($layoutFile, array $vars)
    {
        unset($vars['layoutFile']);
        extract($vars);

        ob_start();

        include $layoutFile;

        return ob_get_clean();
    }

    /**
     *
     * @param SplFileInfo $file
     */
    public function compile(SplFileInfo $file)
    {
        $file = $file->getPathname();

        $config = $this->config;

        // Variables form extensions
        $x = array();





        ob_start();

        include $file;

        $content = ob_get_clean();






        $tmp = substr($file, strlen($config['project.dir']
                . '/' . $config['pages.dir'] . '/'));

        $ptr = str_repeat('../', substr_count($tmp, '/'));

        if ($ptr === '') {
            $ptr = './';
        }

        $vars = array(
            'title'      => $this->title . $config['meta.title.suffix'],
            'content'    => $content,
            'pathToRoot' => $this->pathHelper->normalize($ptr),
            'x'          => $x
        );

        $contentx = $this->fillLayout($config['project.dir']
                    . '/' . $config['layouts.dir'] . '/' . $this->layout, $vars);

        $relativePath = substr($file, strlen($config['project.dir']
                        . '/' . $config['pages.dir'] . '/'));

        $exportPath = $config['project.dir'] . '/' . $config['export.dir']
                . '/' . pathinfo($relativePath, PATHINFO_DIRNAME)
                . '/' . pathinfo($relativePath, PATHINFO_FILENAME)
                . '.' . $config['export.fileExtension'];

        $this->exporter->export($exportPath, $contentx);
    }
}
