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

use App\Entity\Submit;
use App\Event\SubmitStatusChangedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminEventListener implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_UPDATE => 'onUpdate',
        ];
    }

    /**
     * @param GenericEvent<EasyAdminEvents> $event
     */
    public function onUpdate(GenericEvent $event): void
    {
        $entity = $event->getSubject();
        if ($entity instanceof Submit) {
            if ($entity->hasStatusChanged()) {
                $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($entity));
                $entity->resetStatusChanged();
            }
        }
    }
}
