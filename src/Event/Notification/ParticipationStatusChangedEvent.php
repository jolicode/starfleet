<?php

namespace App\Event\Notification;

use App\Entity\Notifications\Notification;
use App\Entity\Participation;
use Symfony\Contracts\EventDispatcher\Event;

class ParticipationStatusChangedEvent extends Event
{
    public function __construct(
        private Participation $participation,
    ) {
    }

    public function getParticipation(): Participation
    {
        return $this->participation;
    }
}
