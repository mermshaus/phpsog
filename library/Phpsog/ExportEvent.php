<?php

namespace Phpsog;

use Symfony\Component\EventDispatcher\Event;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class ExportEvent extends Event
{
    /**
     *
     * @var string
     */
    protected $message = '';

    /**
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
