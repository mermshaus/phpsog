<?php

namespace Phpsog\Command;

use Phpsog\Application;

abstract class AbstractCommand
{
    protected static $shortDescription = '';
    protected static $longDescription = '';

    protected $argv;

    protected $argc;

    public function __construct(array $arguments)
    {
        $this->argv = $arguments;
        $this->argc = count($this->argv);
    }

    abstract public function execute(Application $application);

    public static function getShortDescription()
    {
        return static::$shortDescription;
    }

    public static function getLongDescription()
    {
        return static::$longDescription;
    }
}
