<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Submit;
use App\Event\SubmitStatusChangedEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\GenericEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminEventListener implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_UPDATE => 'onPostUpdate',
            EasyAdminEvents::PRE_PERSIST => 'onPrePersist',
        ];
    }

    /**
     * @param GenericEvent<EasyAdminEvents> $event
     */
    public function onPostUpdate(GenericEvent $event): void
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
}
