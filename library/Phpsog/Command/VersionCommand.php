<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

class VersionCommand extends AbstractCommand
{
    protected static $shortDescription = 'Prints version info and exits.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog version

Prints version info and exists.
EOT;

    public function execute(Application $application)
    {
        $application->getLogger()->log('phpsog version ' . $application->getVersion());
    }
}
