<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

use Exception;

/**
 *
 */
class InitCommand extends AbstractCommand
{
    protected static $shortDescription = 'Sets up a new project.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog init

Sets up a new phpsog project.
EOT;

    /**
     *
     * @param Application $application
     */
    public function execute(Application $application)
    {
        if ($application->ensureProject($application->getProjectDirectory())) {
            throw new Exception('A phpsog project already exists at this location.');
        }

        $exporter = $application->getExporter();

        $exporter->mkdir($application->getProjectDirectory() . '/.phpsog');

        $exporter->export(
                $application->getProjectDirectory() . '/.phpsog/phpsog.ini',
                file_get_contents($application->getPhpsogDirectory()
                        . '/library/Phpsog/config.default.ini'));
    }
}
