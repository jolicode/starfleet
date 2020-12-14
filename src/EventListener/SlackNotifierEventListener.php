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

class SlackNotifierEventListener implements EventSubscriberInterface
{
    private $slackNotifier;
    private $disabled;

    public function __construct(SlackNotifier $slackNotifier)
    {
        $this->slackNotifier = $slackNotifier;
        $this->disabled = false;
    }

    /**
     * Disable the listener, useful while loading fixtures for example.
     */
    public function disable()
    {
        $this->disabled = true;
    }

    public static function getSubscribedEvents()
    {
        return [
            NewTalkSubmittedEvent::class => 'onNewTalkSubmitted',
            NewConferenceEvent::class => 'onNewConferenceAdded',
            NewConferencesEvent::class => 'onNewConferencesAdded',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
            CfpEndingSoonEvent::class => 'onCfpEndingSoon',
        ];
    }

    public function onNewTalkSubmitted(NewTalkSubmittedEvent $event)
    {
        if ($this->disabled) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferenceAdded(NewConferenceEvent $event)
    {
        if ($this->disabled) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferencesAdded(NewConferencesEvent $event)
    {
        if ($this->disabled) {
            return;
        }
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $newConferences = $event->getNewConferences();
        $conferenceAttachment = SlackNotifier::ATTACHMENT;
        $conferenceAttachment['pretext'] = '✨  '.\count($newConferences).' nouvelles conférences ajoutées';

        foreach ($newConferences as $newConference) {
            $conferenceField = $event->buildAttachmentField($newConference);
            $conferenceAttachment['fields'][] = $conferenceField;
        }

        $payload['attachments'][] = $conferenceAttachment;
        $this->slackNotifier->notify($payload);
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event)
    {
        if ($this->disabled) {
            return;
        }

        if (Submit::STATUS_PENDING === $event->getSubmit()->getStatus()) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }

    public function onCfpEndingSoon(CfpEndingSoonEvent $event)
    {
        if ($this->disabled) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $payload['attachments'][] = $event->buildAttachment();
        $this->slackNotifier->notify($payload);
    }
}
