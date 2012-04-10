<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

class UnknownCommand extends AbstractCommand
{
    public function execute(Application $application)
    {
        $logger = $application->getLogger();
        $argv = $this->argv;

        // Unknown -------------------------------------------------------------
        $logger->log(sprintf(
                '\'%s\' is not a phpsog command. See \'phpsog --help\'.',
                $argv[1]));

        if (is_dir($argv[1])) {
            $logger->log("\n" . 'Did you mean \'phpsog build ' . $argv[1] . '\'?');
        }
    }
}
