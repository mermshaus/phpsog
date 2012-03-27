<?php

namespace Phpsog;

use Symfony\Component\EventDispatcher\Event;

class ExportEvent extends Event
{
    protected $message = '';

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}
