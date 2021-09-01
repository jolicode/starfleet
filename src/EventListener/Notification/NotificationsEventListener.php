<?php

namespace App\EventListener\Notification;

use App\Entity\Conference;
use App\Entity\User;
use App\Entity\Submit;
use App\Entity\Notifications\Notification;
use App\Entity\Participation;
use App\Event\Notification\NewFeaturedConferenceEvent;
use App\Event\Notification\SubmitAddedEvent;
use Symfony\Component\Security\Core\Security;
use App\Event\Notification\SubmitStatusChangedEvent;
use Symfony\Component\Serializer\SerializerInterface;
use App\Event\Notification\ParticipationStatusChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationsEventListener implements EventSubscriberInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private Security $security,
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

            $notification = new Notification();
            $notification->setTargetUser($targetUser);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_ADDED);

            $notification->addSerializedObject(Submit::class, $this->serializer->serialize(
                    $event->getSubmit(),
                    'json',
                    [
                        'groups' => ['submitAddedEvent'],
                    ]
                )
            );

            /** @var User $emitter */
            $emitter = $this->security->getUser();
            $notification->addString('emitter', $emitter->getName() ?: $emitter->getEmail());

            $targetUser->addNotification($notification);
            $event->setNotification($notification);
        }
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        foreach ($event->getSubmit()->getUsers() as $targetUser) {
            if ($targetUser === $this->security->getUser()) {
                continue;
            }

            $notification = new Notification();
            $notification->setTargetUser($targetUser);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_STATUS_CHANGED);

            $notification->addSerializedObject(Submit::class, $this->serializer->serialize(
                    $event->getSubmit(),
                    'json',
                    [
                        'groups' => ['submitStatusChangedEvent'],
                    ]
                )
            );

            /** @var User $emitter */
            $emitter = $this->security->getUser();
            $notification->addString('emitter', $emitter->getName() ?: $emitter->getEmail());

            $targetUser->addNotification($notification);
            $event->setNotification($notification);
        }
    }

    public function onParticipationStatusChanged(ParticipationStatusChangedEvent $event): void
    {
        $targetUser = $event->getParticipation()->getParticipant();

        $notification = new Notification();
        $notification->setTargetUser($targetUser);
        $notification->setTrigger(Notification::TRIGGER_PARTICIPATION_STATUS_CHANGED);

        $notification->addSerializedObject(Participation::class, $this->serializer->serialize(
                $event->getParticipation(),
                'json',
                [
                    'groups' => ['participationStatusChangedEvent'],
                ]
            )
        );

        /** @var User $emitter */
        $emitter = $this->security->getUser();
        $notification->addString('emitter', $emitter->getName() ?: $emitter->getEmail());

        $targetUser->addNotification($notification);
        $event->setNotification($notification);
    }

    public function onNewFeaturedConference(NewFeaturedConferenceEvent $event): void
    {
        /** @var User $targetUser */
        $targetUser = $event->getTargetUser();

        $notification = new Notification();
        $notification->setTargetUser($targetUser);
        $notification->setTrigger(Notification::TRIGGER_NEW_FEATURED_CONFERENCE);

        $notification->addSerializedObject(Conference::class, $this->serializer->serialize(
                $event->getConference(),
                'json',
                [
                    'groups' => ['newFeaturedConferenceEvent'],
                ]
            )
        );

        $targetUser->addNotification($notification);
        $event->setNotification($notification);
    }
}
