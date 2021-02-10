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
use Symfony\Contracts\EventDispatcher\Event;

class SubmitStatusChangedEvent extends Event
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

    /** @return array<string,mixed> */
    public function buildAttachment(): array
    {
        $talkAttachment = SlackNotifier::ATTACHMENT;

        if (Submit::STATUS_ACCEPTED === $this->submit->getStatus()) {
            $talkAttachment['pretext'] = '🎉  *Talk accepted*';
        } elseif (Submit::STATUS_REJECTED === $this->submit->getStatus()) {
            $talkAttachment['pretext'] = '😢  *Talk rejected*';
        }

        $talkAttachment['title'] = $this->submit->getTalk()->getTitle();
        $talkAttachment['text'] = $this->submit->getTalk()->getIntro();

        $speakersField = SlackNotifier::LONG_FIELD;
        $speakersField['title'] = \count($this->submit->getUsers()) > 1 ? 'Speakers' : 'Speaker';
        $speakersField['value'] = $this->submit->reduceSpeakersNames();
        $talkAttachment['fields'][] = $speakersField;

        $conferenceField = SlackNotifier::SHORT_FIELD;
        $conferenceField['title'] = 'Conference';
        $conferenceField['value'] = '<'.$this->submit->getConference()->getSiteUrl().'|'.$this->submit->getConference()->getName().'>';
        $talkAttachment['fields'][] = $conferenceField;

        return $talkAttachment;
    }
}
