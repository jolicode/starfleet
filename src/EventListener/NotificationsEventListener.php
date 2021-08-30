<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notifications\SubmitNotification;
use App\Event\Notification\NewSubmitWithAnotherUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationsEventListener implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /** @return array<string> */
    public static function getSubscribedEvents(): array
    {
        return [
            NewSubmitWithAnotherUserEvent::class => 'onNewSubmitWithAnotherUser',
        ];
    }

    public function onNewSubmitWithAnotherUser(NewSubmitWithAnotherUserEvent $event)
    {
        $notification = new SubmitNotification();
        $notification->setTargetUser($event->getTargetUser());
        $notification->setSourceUser($event->getSourceUser());
        $notification->setConference($event->getSubmit()->getConference());
        $notification->setTalk($event->getSubmit()->getTalk());

        $event->getTargetUser()->addNotification($notification);

        $this->em->flush();
    }
}
