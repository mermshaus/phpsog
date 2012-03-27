<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use UnexpectedValueException;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

use Phpsog\Provider\Html\Provider as HtmlProvider;
use Phpsog\Provider\Asset\Provider as AssetProvider;

use Phpsog\Exporter;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Application
{
    const VERSION = '0.1.0';

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
    public function __construct(Exporter $exporter, PathHelper $pathHelper)
    {
        $this->exporter   = $exporter;
        $this->pathHelper = $pathHelper;
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

        if (!file_exists($exportDir)) {
            $this->exporter->mkdir($exportDir);
        }

        if (!is_writable($exportDir)) {
            throw new Exception('export directory '
                    . $this->pathHelper->normalize($exportDir)
                    . ' is not writable');
        }
    }

    /**
     *
     */
    public function processHtmlProvider()
    {
        $config = $this->config;

        $dirIter = new RecursiveDirectoryIterator($config['project.dir']
                 . '/' . $config['pages.dir']);
        $recursiveIterator = new RecursiveIteratorIterator($dirIter,
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD);

        $regexIterator = new RegexIterator($recursiveIterator, '/\.phtml$/i');

        $htmlProvider = new HtmlProvider($this->config, $this->exporter,
                $this->pathHelper);

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
            echo '  Found no resource directory.' . "\n";
            return;
        }

        $recursiveIterator = new RecursiveIteratorIterator($dirIter,
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD);

        $provider = new AssetProvider($this->config, $this->exporter,
                $this->pathHelper);

        foreach ($recursiveIterator as $file => $unused) {
            if (is_file($file)) {
                $provider->compile(new SplFileInfo($file));
            }
        }
    }
}
