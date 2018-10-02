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
use App\Event\NewTalkSubmittedEvent;
use App\Event\SubmitStatusChangedEvent;
use App\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class DoctrineEventSubscriber implements EventSubscriber
{
    private $eventDispatcher;
    private $router;
    private $conferencesAdded = [];

    public function __construct(EventDispatcherInterface $eventDispatcher, RouterInterface $router)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public function getSubscribedEvents()
    {
        return [
            'preFlush',
            'postPersist',
            'preUpdate',
            'postFlush',
        ];
    }

    public function preFlush()
    {
        $this->conferencesAdded = [];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if ($args->getObject() instanceof Submit) {
            $this->eventDispatcher->dispatch(Events::NEW_TALK_SUBMITTED, new NewTalkSubmittedEvent($args->getObject()));
        }

        if ($args->getObject() instanceof Conference) {
            $this->conferencesAdded[] = $args->getObject();
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ($args->getObject() instanceof Submit && $args->hasChangedField('status')) {
            $this->eventDispatcher->dispatch(Events::SUBMIT_STATUS_CHANGED, new SubmitStatusChangedEvent($args->getObject()));
        }
    }

    public function postFlush()
    {
        if (1 === \count($this->conferencesAdded)) {
            $this->eventDispatcher->dispatch(Events::NEW_CONFERENCE_ADDED, new NewConferenceEvent($this->conferencesAdded[0], $this->router));
        } elseif (\count($this->conferencesAdded) > 1) {
            $this->eventDispatcher->dispatch(Events::NEW_CONFERENCES_ADDED, new NewConferencesEvent($this->conferencesAdded, $this->router));
        }
    }
}
