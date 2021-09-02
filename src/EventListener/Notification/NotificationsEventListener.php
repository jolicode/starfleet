<?php

namespace App\EventListener\Notification;

use App\Entity\Conference;
use App\Entity\Notifications\NewFeaturedConferenceNotification;
use App\Entity\User;
use App\Entity\Notifications\Notification;
use App\Entity\Notifications\ParticipationStatusChangedNotification;
use App\Entity\Notifications\SubmitAddedNotification;
use App\Entity\Notifications\SubmitStatusChangedNotification;
use App\Entity\Participation;
use App\Event\Notification\NewFeaturedConferenceEvent;
use App\Event\Notification\SubmitAddedEvent;
use Symfony\Component\Security\Core\Security;
use App\Event\Notification\SubmitStatusChangedEvent;
use App\Event\Notification\ParticipationStatusChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationsEventListener implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em,
    ) {
    }

    /** @return array<string> */
    public static function getSubscribedEvents(): array
    {
        return [
            SubmitAddedEvent::class => 'onSubmitAdded',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
            ParticipationStatusChangedEvent::class => 'onParticipationStatusChanged',
            NewFeaturedConferenceEvent::class => 'onNewFeaturedConference',
        ];
    }

    public function onSubmitAdded(SubmitAddedEvent $event)
    {
        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $this->security->getUser()) {
                continue;
            }

            $notification = new SubmitAddedNotification($event->getSubmit(), $targetUser);
            $notification->setEmitter($targetUser);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_ADDED);
        }
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $this->security->getUser()) {
                continue;
            }

            $notification = new SubmitStatusChangedNotification($event->getSubmit(), $targetUser);
            $notification->setEmitter($targetUser);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_STATUS_CHANGED);
        }
    }

    public function onParticipationStatusChanged(ParticipationStatusChangedEvent $event): void
    {
        $targetUser = $event->getParticipation()->getParticipant();

        $notification = new ParticipationStatusChangedNotification($event->getParticipation(), $targetUser);
        $notification->setEmitter($this->security->getUser());
        $notification->setTrigger(Notification::TRIGGER_PARTICIPATION_STATUS_CHANGED);
    }

    public function onNewFeaturedConference(NewFeaturedConferenceEvent $event): void
    {
        $query = $this->em->createQuery('select u from App\Entity\User u');

        foreach ($query->toIterable() as $targetUser) {
            $notification = new NewFeaturedConferenceNotification($event->getConference(), $targetUser);
            $notification->setTrigger(Notification::TRIGGER_NEW_FEATURED_CONFERENCE);
        }
    }
}
