<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

use Phpsog\ExportEvent;

use Exception;

/**
 *
 */
class BuildCommand extends AbstractCommand
{
    protected static $shortDescription = 'Compiles a project.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog build [--clean]

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

        if (!$application->ensureProject($application->getProjectDirectory())) {
            throw new Exception('There is no .phpsog directory.');
        }

        $params = array();

        $params['config'] = $application->getProjectDirectory() . '/.phpsog/phpsog.ini';

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
