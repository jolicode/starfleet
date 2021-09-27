<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\EventListener\Notification;

use App\Entity\Notifications\AbstractNotification;
use App\Entity\Notifications\NewFeaturedConferenceNotification;
use App\Entity\Notifications\NewSubmitNotification;
use App\Entity\Notifications\ParticipationStatusChangedNotification;
use App\Entity\Notifications\SubmitCancelledNotification;
use App\Entity\Notifications\SubmitStatusChangedNotification;
use App\Entity\User;
use App\Event\Notification\NewFeaturedConferenceEvent;
use App\Event\Notification\NewSubmitEvent;
use App\Event\Notification\ParticipationStatusChangedEvent;
use App\Event\Notification\SubmitCancelledEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

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
            NewSubmitEvent::class => 'onNewSubmit',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
            ParticipationStatusChangedEvent::class => 'onParticipationStatusChanged',
            NewFeaturedConferenceEvent::class => 'onNewFeaturedConference',
            SubmitCancelledEvent::class => 'onSubmitCancelled',
        ];
    }

    public function onNewSubmit(NewSubmitEvent $event): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $currentUser) {
                continue;
            }

            $notification = new NewSubmitNotification(
                submit: $event->getSubmit(),
                emitter: $currentUser,
                targetUser: $targetUser,
                trigger: AbstractNotification::TRIGGER_NEW_SUBMIT
            );

            $this->em->persist($notification);
        }
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $currentUser) {
                continue;
            }

            $notification = new SubmitStatusChangedNotification(
                submit: $event->getSubmit(),
                emitter: $currentUser,
                targetUser: $targetUser,
                trigger: AbstractNotification::TRIGGER_SUBMIT_STATUS_CHANGED
            );

            $this->em->persist($notification);
        }
    }

    public function onParticipationStatusChanged(ParticipationStatusChangedEvent $event): void
    {
        $targetUser = $event->getParticipation()->getParticipant();
        /** @var User $emitter */
        $emitter = $this->security->getUser();

        $notification = new ParticipationStatusChangedNotification(
            participation: $event->getParticipation(),
            emitter: $emitter,
            targetUser: $targetUser,
            trigger: AbstractNotification::TRIGGER_PARTICIPATION_STATUS_CHANGED
        );

        $this->em->persist($notification);
    }

    public function onNewFeaturedConference(NewFeaturedConferenceEvent $event): void
    {
        $query = $this->em->createQuery('select u from App\Entity\User u');

        foreach ($query->toIterable() as $targetUser) {
            $notification = new NewFeaturedConferenceNotification($event->getConference(), $targetUser, AbstractNotification::TRIGGER_NEW_FEATURED_CONFERENCE);

            $this->em->persist($notification);
        }
    }

    public function onSubmitCancelled(SubmitCancelledEvent $event): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $currentUser) {
                continue;
            }

            $notification = new SubmitCancelledNotification(
                talk: $event->getTalk(),
                conference: $event->getConference(),
                emitter: $currentUser,
                targetUser: $targetUser,
                trigger: AbstractNotification::TRIGGER_SUBMIT_CANCELLED,
            );

            $this->em->persist($notification);
        }
    }
}
