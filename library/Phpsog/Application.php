<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use UnexpectedValueException;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use Phpsog\Provider\Html\Provider as HtmlProvider;

use Phpsog\Exporter;

/**
 *
 * @param  string $s
 * @return string
 */
function e($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Application
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     *
     * @var Exporter
     */
    protected $exporter;

    /**
     *
     */
    public function __construct()
    {
        $this->pathHelper = new PathHelper();
        $this->exporter = new Exporter($this->pathHelper);
    }

    /**
     *
     * @param  string $pathToConfig
     * @return array
     */
    public function loadConfig($pathToConfig)
    {
        $pathToConfig = realpath($pathToConfig);

        $projectDir = realpath(dirname($pathToConfig));

        $defaultConfig = parse_ini_file(__DIR__ . '/config.default.ini');

        $projectConfig = parse_ini_file($pathToConfig);

        $projectConfig += $defaultConfig;

        $projectConfig['project.dir'] = $projectDir;

        date_default_timezone_set($projectConfig['general.timezone']);

        $this->config = $projectConfig;

        return $projectConfig;
    }

    /**
     *
     */
    public function sanitizeEnvironment()
    {
        $config = $this->config;

        $exportDir = $config['project.dir'] . '/' . $config['export.dir'];

        if (!is_writable($exportDir)) {
            throw new Exception('export directory '
                    . $this->pathHelper->normalize($exportDir)
                    . ' is not writable');
        }
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
     */
    public function processHtmlProvider()
    {
        $config = $this->config;

        $dirIter = new \RecursiveDirectoryIterator($config['project.dir']
                 . '/' . $config['pages.dir']);
        $recursiveIterator = new \RecursiveIteratorIterator($dirIter,
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD);

        $regexIterator = new \RegexIterator($recursiveIterator, '/\.phtml$/i');

        $htmlProvider = new HtmlProvider($this->exporter, $this->config);

        foreach ($regexIterator as $file => $unused) {
            $htmlProvider->setLayout('default.phtml');
            $htmlProvider->setTitle($config['meta.title.default']);
            $htmlProvider->compile(new SplFileInfo($file));
        }
    }

    /**
     *
     */
    public function processResources()
    {
        $config = $this->config;

        $dirIter = null;

        try {
            $dirIter = new RecursiveDirectoryIterator($config['project.dir']
                     . '/' . $config['resources.dir']);
        } catch (UnexpectedValueException $e) {
            echo '  Found no resource directory.' . PHP_EOL;
            return;
        }

        $recursiveIterator = new RecursiveIteratorIterator($dirIter,
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD);

        foreach ($recursiveIterator as $file => $unused) {

            if (!is_file($file)) {
                continue;
            }

            $relativePath = substr($file, strlen($config['project.dir']
                          . '/' . $config['resources.dir'] . '/'));

            $exportPath = $config['project.dir'] . '/' . $config['export.dir']
                        . '/' . pathinfo($relativePath, PATHINFO_DIRNAME)
                        . '/' . basename($relativePath);

            $content = file_get_contents($file);

            $this->exporter->export($exportPath, $content);
        }
    }

    /**
     *
     * @param  array  $config
     * @param  string $content
     * @param  string $title
     * @param  string $layout
     * @param  string $pathToRoot
     * @param  array  $x
     * @return string
     */
    public function addVirtualPage(array $config, $content, $title = null,
            $layout = null, $pathToRoot = '.', $x = array())
    {
        $ph = $this->pathHelper;

        if ($title === null) {
            $title = $config['meta.title.default'];
        }

        if ($layout === null) {
            $layout = 'default.phtml';
        }

        $vars = array(
            'title'      => $title . $config['meta.title.suffix'],
            'content'    => $content,
            'pathToRoot' => $ph->normalize($pathToRoot),
            'x'          => $x
        );

        return $this->fillLayout($config['project.dir'] . '/'
                . $config['layouts.dir'] . '/' . $layout, $vars);
    }
}
