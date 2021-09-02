<?php

namespace App\Event\Notification;

use App\Entity\Submit;
use Symfony\Contracts\EventDispatcher\Event;

class SubmitAddedEvent extends Event
{
    public function __construct(
        private Submit $submit,
    ) {
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }
}
