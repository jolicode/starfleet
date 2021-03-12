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
use App\Entity\Talk;
use App\SlackNotifier;
use Symfony\Contracts\EventDispatcher\Event;

class NewTalkSubmittedEvent extends Event
{
    private Talk $talk;
    /** @var array<Submit> */
    private array $submits;

    /** @param array<Submit> $submits */
    public function __construct(Talk $talk, array $submits)
    {
        $this->talk = $talk;
        $this->submits = $submits;
    }

    /** @return array<array> */
    public function buildAttachment(): array
    {
        $talkAttachment = SlackNotifier::ATTACHMENT;
        $talkAttachment['pretext'] = '🗣  *New submitted talk*';
        $talkAttachment['title'] = $this->talk->getTitle();
        $talkAttachment['text'] = $this->talk->getIntro();

        $submitsAttachment = SlackNotifier::ATTACHMENT;
        $submitsAttachment['title'] = 'Submitted at : ';

        foreach ($this->submits as $submit) {
            $conferenceField = SlackNotifier::LONG_FIELD;
            $conference = '<'.$submit->getConference()->getSiteUrl().'|'.$submit->getConference()->getName().'>';
            $status = Submit::STATUS_EMOJIS[$submit->getStatus()];
            $author = $submit->reduceSpeakersNames();

            $conferenceField['value'] = sprintf('%s (%s) by %s', $conference, $status, $author);
            $submitsAttachment['fields'][] = $conferenceField;
        }

        return [$talkAttachment, $submitsAttachment];
    }
}
