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
use App\Event\NewConferenceEvent;
use App\Event\NewConferencesEvent;
use App\Event\NewTalkSubmittedEvent;
use App\Event\SubmitStatusChangedEvent;
use App\SlackNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class SlackNotifierEventListener implements EventSubscriberInterface
{
    private SlackNotifier $slackNotifier;
    private RouterInterface $router;
    private bool $disabled;

    public function __construct(SlackNotifier $slackNotifier, RouterInterface $router)
    {
        $this->slackNotifier = $slackNotifier;
        $this->router = $router;
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
            NewConferenceEvent::class => 'onNewConferenceAdded',
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

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferenceAdded(NewConferenceEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment($this->router);
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferencesAdded(NewConferencesEvent $event): void
    {
        if ($this->disabled) {
            return;
        }
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $newConferences = $event->getNewConferences();
        $conferenceAttachment = SlackNotifier::ATTACHMENT;
        $conferenceAttachment['pretext'] = '✨  '.\count($newConferences).' nouvelles conférences ajoutées';

        foreach ($newConferences as $newConference) {
            $conferenceField = $event->buildAttachmentField($newConference, $this->router);
            $conferenceAttachment['fields'][] = $conferenceField;
        }

        $payload['attachments'][] = $conferenceAttachment;
        $this->slackNotifier->notify($payload);
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

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }

    public function onCfpEndingSoon(CfpEndingSoonEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }
}
