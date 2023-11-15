<?php

namespace App\Event\Notification;

use App\Entity\Conference;
use Symfony\Contracts\EventDispatcher\Event;

class NewFeaturedConferenceEvent extends Event
{
    public function __construct(
        private Conference $conference,
    ) {
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }
}
