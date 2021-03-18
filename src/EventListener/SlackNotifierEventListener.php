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
use App\Event\CfpEndingSoonEvent;
use App\Event\NewConferencesEvent;
use App\Event\NewTalkSubmittedEvent;
use App\Event\SubmitStatusChangedEvent;
use App\SlackNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlackNotifierEventListener implements EventSubscriberInterface
{
    private SlackNotifier $slackNotifier;
    private bool $disabled;

    public function __construct(SlackNotifier $slackNotifier)
    {
        $this->slackNotifier = $slackNotifier;
        $this->disabled = false;
    }

    /**
     * Disable the listener, useful while loading fixtures for example.
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /** @return array<string,string> */
    public static function getSubscribedEvents(): array
    {
        return [
            NewTalkSubmittedEvent::class => 'onNewTalkSubmitted',
            NewConferencesEvent::class => 'onNewConferencesAdded',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
            CfpEndingSoonEvent::class => 'onCfpEndingSoon',
        ];
    }

    public function onNewTalkSubmitted(NewTalkSubmittedEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $this->slackNotifier->sendNewTalkSubmittedNotification($event->getSubmits(), $event->getTalk());
    }

    public function onNewConferencesAdded(NewConferencesEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $this->slackNotifier->sendNewConferencesNotification($event->getNewConferences());
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $status = $event->getSubmit()->getStatus();

        if (Submit::STATUS_PENDING === $status || Submit::STATUS_DONE === $status) {
            return;
        }

        $this->slackNotifier->sendSubmitStatusChangedNotification($event->getSubmit());
    }

    public function onCfpEndingSoon(CfpEndingSoonEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $this->slackNotifier->sendCfPEndingSoonNotification($event->getConference(), $event->getRemainingDays());
    }
}
