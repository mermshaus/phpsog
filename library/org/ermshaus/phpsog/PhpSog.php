<?php
/**
 *
 */

namespace org\ermshaus\phpsog;

use \Kaloa\Filesystem\PathHelper;

/**
 *
 */
class PhpSog
{
    /** @var array */
    protected $config;

    /** @var PathHelper */
    protected $pathHelper;

    /**
     *
     */
    public function __construct()
    {
        $this->pathHelper = new PathHelper();
    }

    /**
     *
     * @param type $pathToConfig
     * @return type
     */
    public function loadConfig($pathToConfig)
    {
        $pathToConfig = realpath($pathToConfig);

        $projectDir = realpath(dirname($pathToConfig));

        $defaultConfig = parse_ini_file('./config.default.ini');

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
            throw new \Exception('export directory ' . $exportDir . ' is not writable');
        }
    }

    /**
     *
     * @param type $layoutFile
     * @param array $vars
     * @return type
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
     * @param array $config
     * @param type $file
     */
    public function processFiles()
    {
        $config = $this->config;

        $ph = $this->pathHelper;

        $dirIter = new \RecursiveDirectoryIterator($config['project.dir'] . '/' . $config['pages.dir']);
        $recursiveIterator = new \RecursiveIteratorIterator($dirIter,
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD);

        $regexIterator = new \RegexIterator($recursiveIterator, '/\.phtml$/i');

        foreach ($regexIterator as $file => $unused) {
                $layout = 'default.phtml';
            $title  = $config['meta.title.default'];

            // Variables form extensions
            $x = array();

            ob_start();

            include $file;

            $content = ob_get_clean();
            $tmp = substr($file, strlen($config['project.dir'] . '/' . $config['pages.dir'] . '/'));

            $ptr = str_repeat('../', substr_count($tmp, '/'));

            if ($ptr === '') {
                $ptr = './';
            }

            $vars = array(
                'title'      => $title . $config['meta.title.suffix'],
                'content'    => $content,
                'pathToRoot' => $ph->normalize($ptr),
                'x'          => $x
            );

            $contentx = $this->fillLayout($config['project.dir'] . '/'
                    . $config['layouts.dir'] . '/' . $layout, $vars);

            $relativePath = substr($file, strlen($config['project.dir'] . '/' . $config['pages.dir'] . '/'));

            $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
                    . pathinfo($relativePath, PATHINFO_DIRNAME) . '/' . pathinfo($relativePath, PATHINFO_FILENAME)
                    . '.' . $config['export.fileExtension'];

            $this->export($exportPath, $contentx);
        }
    }

    /**
     *
     * @param type $config
     */
    public function processResources()
    {
        $config = $this->config;

        $dirIter = new \RecursiveDirectoryIterator($config['project.dir'] . '/' . $config['resources.dir']);
        $recursiveIterator = new \RecursiveIteratorIterator($dirIter,
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD);

        foreach ($recursiveIterator as $file => $unused) {

            if (!is_file($file)) {
                continue;
            }

            $relativePath = substr($file, strlen($config['project.dir'] . '/' . $config['resources.dir'] . '/'));

            $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
                    . pathinfo($relativePath, PATHINFO_DIRNAME) . '/' . basename($relativePath);

            $content = file_get_contents($file);

            $this->export($exportPath, $content);
        }
    }

    /**
     *
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

    /**
     *
     * @param array $config
     * @param type $content
     * @param array $title
     * @param string $layout
     * @param type $pathToRoot
     * @param type $x
     * @return type
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
