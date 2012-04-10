<?php

namespace Phpsog;

use Kaloa\Filesystem\PathHelper;
use Phpsog\Application;
use Phpsog\Exporter;
use Phpsog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Factory
{
    /**
     *
     * @return Application
     */
    public function createNewApplication()
    {
        $logger      = new Logger();
        $ph          = new PathHelper();
        $dispatcher  = new EventDispatcher();
        $exporter    = new Exporter($ph, $dispatcher);
        $application = new Application($logger, $dispatcher, $exporter, $ph);

        return $application;
    }
}
