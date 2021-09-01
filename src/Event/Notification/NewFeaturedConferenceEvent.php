<?php

namespace App\Event\Notification;

use App\Entity\Conference;
use App\Entity\Notifications\Notification;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class NewFeaturedConferenceEvent extends Event
{
    private Notification $notification;

    public function __construct(
        private Conference $conference,
        private UserInterface $targetUser,
    ) {
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getTargetUser(): UserInterface
    {
        return $this->targetUser;
    }
}
