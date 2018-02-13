<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\Submit;
use App\SlackNotifier;
use Symfony\Component\EventDispatcher\Event;

class NewTalkSubmittedEvent extends Event
{
    private $submit;

    public function __construct(Submit $submit)
    {
        $this->submit = $submit;
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function buildAttachment(): array
    {
        $talkAttachment = SlackNotifier::ATTACHMENT;
        $talkAttachment['pretext'] = '🗣  *New submitted talk*';
        $talkAttachment['title'] = $this->submit->getTalk()->getTitle();
        $talkAttachment['text'] = $this->submit->getTalk()->getIntro();

        $speakersField = SlackNotifier::LONG_FIELD;
        $speakersField['title'] = count($this->submit->getUsers()) > 1 ? 'Speakers' : 'Speaker';
        $speakersField['value'] = $this->submit->reduceSpeakersNames();
        array_push($talkAttachment['fields'], $speakersField);

        $statusField = SlackNotifier::SHORT_FIELD;
        $statusField['title'] = 'Status';
        $statusField['value'] = Submit::STATUS_EMOJIS[$this->submit->getStatus()];
        array_push($talkAttachment['fields'], $statusField);

        $conferenceField = SlackNotifier::SHORT_FIELD;
        $conferenceField['title'] = 'Conference';
        $conferenceField['value'] = '<'.$this->submit->getConference()->getSiteUrl().'|'.$this->submit->getConference()->getName().'>';
        array_push($talkAttachment['fields'], $conferenceField);

        return $talkAttachment;
    }
}
