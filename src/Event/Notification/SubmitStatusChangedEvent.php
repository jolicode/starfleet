<?php

namespace App\Event\Notification;

use App\Entity\Submit;
use App\Entity\Notifications\Notification;
use Symfony\Contracts\EventDispatcher\Event;

class SubmitStatusChangedEvent extends Event
{
    private Notification $notification;

    public function __construct(
        private Submit $submit,
    ) {
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }
}
