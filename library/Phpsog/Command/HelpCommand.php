<?php

namespace Phpsog\Command;

use Phpsog\Command\AbstractCommand;

use Phpsog\Application;

/**
 *
 */
class HelpCommand extends AbstractCommand
{
    protected static $shortDescription = 'Prints this usage information.';

    protected static $longDescription = <<<'EOT'
Usage: phpsog help [<command>]

   <command>  The command to display usage information about.
EOT;

    /**
     *
     * @param Application $application
     */
    public function execute(Application $application)
    {
        $logger = $application->getLogger();

        if ($this->argc === 2) {
            $commands = array();

            foreach (glob(__DIR__ . '/*Command.php') as $file) {
                $commandName = preg_replace('/^.*?([a-z]+)Command\.php$/i', '$1', $file);

                if ($commandName === 'Abstract' || $commandName === 'Unknown') {
                    continue;
                }

                $className = __NAMESPACE__ . '\\' . $commandName . 'Command';

                $commands[] = array(
                    'switches'    => strtolower($commandName),
                    'description' => $className::getShortDescription()
                );
            }

            $logger->log('Usage: phpsog [--version] [--help] [<command>] [<args>]' . "\n");

            $logger->log('The commands are:');

            foreach ($commands as $command) {
                $logger->log('   ' . $command['switches']
                        . str_repeat(' ', 11 - strlen($command['switches']))
                        . $command['description']);
            }

            $logger->log("\n"
                    . 'See \'phpsog help <command>\' for more information on a specific command.');
        } else if ($this->argc === 3) {

            $className = __NAMESPACE__ . '\\' . ucfirst($this->argv[2]) . 'Command';

            $noEntry = false;

            if (class_exists($className)) {
                if ($className::getLongDescription() !== '') {
                    $logger->log($className::getLongDescription());
                } else {
                    $noEntry = true;
                }
            } else {
                $noEntry = true;
            }

            if ($noEntry) {
                $logger->log('No manual entry for \'' . $this->argv[2] . '\'');
            }
        }
    }
}
