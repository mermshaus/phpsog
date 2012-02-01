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
        $x = array();

        ob_start();

        include $file;

        $content = ob_get_clean();

        $vars = array(
            'title'   => $title . $config['meta.title.suffix'],
            'content' => $content,
            'x'       => $x
        );

        return $this->fillLayout($config['project.dir'] . '/'
                . $config['layouts.dir'] . '/' . $layout, $vars);
    }

    public function addVirtualPage(array $config, $content, $title = null, $layout = null, $x = array())
    {
        if ($title === null) {
            $title = $config['meta.title.default'];
        }

        if ($layout === null) {
            $layout = 'default.phtml';
        }

        $vars = array(
            'title'   => $title . $config['meta.title.suffix'],
            'content' => $content,
            'x'       => $x
        );

        return $this->fillLayout($config['project.dir'] . '/'
                . $config['layouts.dir'] . '/' . $layout, $vars);
    }
}
