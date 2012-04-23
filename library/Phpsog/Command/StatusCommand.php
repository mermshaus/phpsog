<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

use Kaloa\Filesystem\PathHelper;

/**
 *
 */
class StatusCommand extends AbstractCommand
{
    protected static $shortDescription = 'Prints status information about a project.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog status

Prints status information about a project.
EOT;

    /**
     *
     * @param Application $application
     */
    public function execute(Application $application)
    {
        $l = $application->getLogger();

        if (!$application->ensureProject($application->getProjectDirectory())) {
            $l->log('No phpsog project found at ' . realpath('.'));
        } else {
            $l->log('This is a phpsog project.');

            $conf = $application->loadConfig($application->getProjectDirectory() . '/.phpsog/phpsog.ini');

            ksort($conf);

            foreach ($conf as $k => $v) {
                $l->log('   ' . $k . str_repeat(' ', 20 - strlen($k)) . ' => ' . $v);
            }

            $ph = new PathHelper();

            $l->log('Will export to: ' . $ph->normalize($conf['project.dir']
                    . '/' . $conf['export.dir']));
        }
    }
}
