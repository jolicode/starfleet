<?php

namespace App\Event\Notification;

use App\Entity\Submit;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SubmitAddedEvent extends Event
{
    public function __construct(
        private Submit $submit,
        private User $emitterUser,
    ) {
    }

    public function getEmitterUser(): User
    {
        return $this->emitterUser;
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }
}
