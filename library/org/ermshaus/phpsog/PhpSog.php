<?php

namespace org\ermshaus\phpsog;

class PhpSog
{
    public function loadConfig($pathToConfig)
    {
        $pathToConfig = realpath($pathToConfig);

        $projectDir = realpath(dirname($pathToConfig));

        $defaultConfig = parse_ini_file('./config.default.ini');

        $projectConfig = parse_ini_file($pathToConfig);

        $projectConfig += $defaultConfig;

        $projectConfig['project.dir'] = $projectDir;

        return $projectConfig;
    }

    protected function fillLayout($layoutFile, array $vars)
    {
        unset($vars['layoutFile']);
        extract($vars);

        ob_start();

        include $layoutFile;

        return ob_get_clean();
    }

    public function processFile(array $config, $file)
    {
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
            'pathToRoot' => $ptr,
            'x'          => $x
        );

        return $this->fillLayout($config['project.dir'] . '/'
                . $config['layouts.dir'] . '/' . $layout, $vars);
    }
}
