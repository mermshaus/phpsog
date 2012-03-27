<?php

namespace Phpsog\Provider\Html;

class View
{
    protected $vars;
    protected $scriptFile;

    public function assign($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function get($key)
    {
        return (isset($this->vars[$key])) ? $this->vars[$key] : null;
    }

    public function setScript($scriptFile)
    {
        $this->scriptFile = $scriptFile;
    }

    public function render()
    {
        ob_start();

        include $this->scriptFile;

        return ob_get_clean();
    }
}
