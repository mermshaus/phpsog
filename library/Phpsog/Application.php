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

use Phpsog\Logger;

use Phpsog\Command\AbstractCommand;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Application
{
    /**
     * The package's version string
     */
    const VERSION = '0.1.0';

    /**
     * @var array
     */
    protected $config = array();

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     *
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
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     *
     * @var string
     */
    protected $phpsogDirectory;

    /**
     *
     * @var string
     */
    protected $projectDirectory;

    /**
     *
     * @param Logger     $logger
     * @param EventDispatcherInterface $dispatcher
     * @param Exporter   $exporter
     * @param PathHelper $pathHelper
     * @param string     $phpsogDirectory
     * @param string     $projectDirectory
     */
    public function __construct(Logger $logger, EventDispatcherInterface $dispatcher,
            Exporter $exporter, PathHelper $pathHelper, $phpsogDirectory, $projectDirectory)
    {
        $this->logger     = $logger;
        $this->exporter   = $exporter;

        $this->dispatcher = $dispatcher;

        $this->pathHelper = $pathHelper;

        if (substr($phpsogDirectory, 0, 1) !== '/') {
            $phpsogDirectory = getcwd() . '/' . $phpsogDirectory;
        }

        if (substr($projectDirectory, 0, 1) !== '/') {
            $projectDirectory = getcwd() . '/' . $projectDirectory;
        }

        $phpsogDirectory = $pathHelper->normalize($phpsogDirectory);
        $projectDirectory = $pathHelper->normalize($projectDirectory);

        if (!is_dir($phpsogDirectory)) {
            throw new Exception('phpsog directory is not a valid directory');
        }

        if (!is_dir($projectDirectory)) {
            throw new Exception('project directory is not a valid directory');
        }

        $this->phpsogDirectory = $phpsogDirectory;
        $this->projectDirectory = $projectDirectory;
    }

    /**
     *
     * @param  string $pathToConfig
     * @return array
     */
    public function loadConfig($pathToConfig)
    {
        $pathToConfig = realpath($pathToConfig);

        $projectDir = realpath(dirname($pathToConfig) . '/../');

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

    /**
     *
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     *
     * @param AbstractCommand $cmd
     */
    public function executeCommand(AbstractCommand $cmd)
    {
        $cmd->execute($this);
    }

    /**
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     *
     * @return Exporter
     */
    public function getExporter()
    {
        return $this->exporter;
    }

    /**
     *
     * @param string $path
     */
    public function ensureProject($path)
    {
        $path .= '/.phpsog';

        return (file_exists($path) && is_dir($path));
    }

    /**
     *
     * @return string
     */
    public function getPhpsogDirectory()
    {
        return $this->phpsogDirectory;
    }

    /**
     *
     * @return string
     */
    public function getProjectDirectory()
    {
        return $this->projectDirectory;
    }
}
