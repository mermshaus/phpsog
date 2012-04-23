<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use Phpsog\Application;
use Phpsog\Exporter;
use Phpsog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 *
 */
class Factory
{
    /**
     *
     * @return Application
     */
    public function createNewApplication($phpsogDirectory = null, $projectDirectory = null)
    {
        if ($phpsogDirectory === null) {
            $phpsogDirectory = getcwd();
        }

        if ($projectDirectory === null) {
            $projectDirectory = getcwd();
        }

        $logger      = new BufferLogger();
        $ph          = new PathHelper();
        $dispatcher  = new EventDispatcher();
        $exporter    = new Exporter($ph, $dispatcher);
        $application = new Application($logger, $dispatcher, $exporter, $ph,
                $phpsogDirectory, $projectDirectory);

        return $application;
    }
}
