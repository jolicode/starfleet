<?php

namespace App\Event\Notification;

use App\Entity\Submit;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class NewSubmitWithAnotherUserEvent extends Event
{
    public function __construct(
        private Submit $submit,
        private User $sourceUser,
        private User $targetUser,
    ) {
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function getSourceUser(): User
    {
        return $this->sourceUser;
    }

    public function getTargetUser(): User
    {
        return $this->targetUser;
    }
}
