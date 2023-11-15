<?php

namespace App\Event\Notification;

use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use Symfony\Contracts\EventDispatcher\Event;

class SubmitCancelledEvent extends Event
{
    private Talk $talk;
    private Conference $conference;

    public function __construct(
        private Submit $submit,
    ) {
        $this->talk = $submit->getTalk();
        $this->conference = $submit->getConference();
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function getTalk(): Talk
    {
        return $this->talk;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }
}
