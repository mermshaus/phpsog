<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

use Phpsog\ExportEvent;

/**
 *
 */
class BuildCommand extends AbstractCommand
{
    protected static $shortDescription = 'Compiles a project.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog build [--clean] [<path>]

   <path>     Path to valid config file or directory with phpsog.ini file.
   --clean    Delete and recreate export directory before export.
EOT;

    /**
     *
     * @param Application $application
     */
    public function execute(Application $application)
    {
        $logger = $application->getLogger();
        $dispatcher = $application->getDispatcher();
        $argv = $this->argv;

        $params = array(
            'config' => $argv[2]
        );

        if (is_dir($params['config'])) {
            $params['config'] .= '/phpsog.ini';
        }

        $adapt = function (ExportEvent $event) use ($logger) {
            $logger->onExport($event);
        };
        $dispatcher->addListener('onExport', $adapt);
        $dispatcher->addListener('onMkdir', $adapt);

        $exportCounter = 0;
        $mkdirCounter = 0;

        $dispatcher->addListener('onExport', function () use (&$exportCounter) {
            $exportCounter++;
        });
        $dispatcher->addListener('onMkdir', function () use (&$mkdirCounter) {
            $mkdirCounter++;
        });

        $application->loadConfig($params['config']);

        $application->sanitizeEnvironment();

        $application->processHtmlProvider();
        $application->processResources();

        $logger->log("\n" . 'Event count:    Export: ' . $exportCounter
                . '    Mkdir: ' . $mkdirCounter);
    }
}
