<?php

namespace Phpsog;

use Phpsog\ExportEvent;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Logger
{
    public function onExport(ExportEvent $event)
    {
        $this->log($event->getMessage());
    }

    public function log($text)
    {
        $text = preg_replace('/^/m', '  ', $text);

        echo $text . "\n";
    }
}
