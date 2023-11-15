<?php

namespace App\EventListener;

use App\Entity\Submit;
use App\Entity\User;
use App\Event\Notification\NewSubmitEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Security;

class EasyAdminEventListener implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EasyAdminEvents::PRE_UPDATE => 'onPreUpdate',
            EasyAdminEvents::PRE_PERSIST => 'onPrePersist',
            EasyAdminEvents::POST_PERSIST => 'onPostPersist',
        ];
    }

    /**
     * @param GenericEvent<EasyAdminEvents> $event
     */
    public function onPreUpdate(GenericEvent $event): void
    {
        $entity = $event->getSubject();
        if ($entity instanceof Submit) {
            if ($entity->hasStatusChanged()) {
                $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($entity));
                $entity->resetStatusChanged();
            }
        }
    }

    public function onPrePersist(GenericEvent $event): void
    {
        $entity = $event->getSubject();
        if ($entity instanceof Submit) {
            /** @var User $user */
            $user = $this->security->getUser();
            $entity->setSubmittedBy($user);
        }
    }

    public function onPostPersist(GenericEvent $event): void
    {
        $entity = $event->getSubject();

        if ($entity instanceof Submit) {
            $this->eventDispatcher->dispatch(new NewSubmitEvent($entity));
        }
    }
}
