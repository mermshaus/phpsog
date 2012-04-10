<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

/**
 *
 */
class InitCommand extends AbstractCommand
{
    protected static $shortDescription = 'Sets up a new project.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog init [<path>]

   <path>     Path to create a new project in.
EOT;

    /**
     *
     * @param Application $application
     */
    public function execute(Application $application)
    {
        $application->getLogger()->log('Create project...');
    }
}
