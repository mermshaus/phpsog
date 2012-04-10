<?php

namespace Phpsog;

use Phpsog\ExportEvent;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class BufferLogger extends Logger
{
    protected $log = '';

    public function onExport(ExportEvent $event)
    {
        $this->log($event->getMessage());
    }

    public function log($text)
    {
        //$text = preg_replace('/^/m', '  ', $text);

        $this->log .= $text . "\n";
    }

    public function getLog()
    {
        return $this->log;
    }
}
