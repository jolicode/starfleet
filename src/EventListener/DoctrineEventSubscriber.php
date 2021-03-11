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

use App\Entity\Conference;
use App\Entity\Submit;
use App\Event\NewConferenceEvent;
use App\Event\NewConferencesEvent;
use App\Event\SubmitStatusChangedEvent;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class DoctrineEventSubscriber implements EventSubscriber
{
    private EventDispatcherInterface $eventDispatcher;
    private RouterInterface $router;
    /** @var array<Conference> */
    private array $conferencesAdded = [];

    public function __construct(EventDispatcherInterface $eventDispatcher, RouterInterface $router)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    /** @return array<string> */
    public function getSubscribedEvents(): array
    {
        return [
            'preFlush',
            'prePersist',
            'postPersist',
            'preUpdate',
            'postFlush',
        ];
    }

    public function preFlush(): void
    {
        $this->conferencesAdded = [];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        if (method_exists($args->getObject(), 'setCreatedAt')) {
            $args->getObject()->setCreatedAt(new \DateTime());
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if ($args->getObject() instanceof Conference) {
            $this->conferencesAdded[] = $args->getObject();
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if ($args->getObject() instanceof Submit && $args->hasChangedField('status')) {
            $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($args->getObject()));
        }

        if (method_exists($args->getObject(), 'setUpdatedAt')) {
            $args->getObject()->setUpdatedAt(new \DateTime());
        }
    }

    public function postFlush(): void
    {
        if (1 === \count($this->conferencesAdded)) {
            $this->eventDispatcher->dispatch(new NewConferenceEvent($this->conferencesAdded[0], $this->router));
        } elseif (\count($this->conferencesAdded) > 1) {
            $this->eventDispatcher->dispatch(new NewConferencesEvent($this->conferencesAdded, $this->router));
        }
    }
}
