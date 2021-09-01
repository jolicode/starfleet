<?php

namespace App\EventListener\Notification;

use App\Entity\Notifications\Notification;
use App\Entity\Submit;
use App\Event\Notification\SubmitAddedEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

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
        ];
    }

    public function onSubmitAdded(SubmitAddedEvent $event): void
    {
        foreach ($event->getSubmit()->getUsers() as $user) {
            if ($user === $this->security->getUser()) {
                continue;
            }

            $notification = new Notification();
            $notification->setTargetUser($user);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_ADDED);

            $serializedSubmit = $this->serializer->serialize(
                $event->getSubmit(),
                'json',
                ['groups' => ['submitAddedEvent']],
            );

            $serializedUser = $this->serializer->serialize(
                $event->getEmitterUser(),
                'json',
                ['groups' => ['submitAddedEvent']],
            );

            $notification->setData([
                Submit::class => $serializedSubmit,
                User::class => $serializedUser,
            ]);

            $user->addNotification($notification);
        }
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        foreach ($event->getSubmit()->getUsers() as $user) {
            if ($user === $this->security->getUser()) {
                continue;
            }

            $notification = new Notification();
            $notification->setTargetUser($user);
            $notification->setTrigger(Notification::TRIGGER_SUBMIT_STATUS_CHANGED);

            $serializedSubmit = $this->serializer->serialize(
                $event->getSubmit(),
                'json',
                ['groups' => ['submitStatusChangedEvent']]
            );
            $notification->setData([Submit::class => $serializedSubmit]);

            $user->addNotification($notification);
        }

    }
}
