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

use App\Entity\Conference;
use App\SlackNotifier;
use Symfony\Contracts\EventDispatcher\Event;

class CfpEndingSoonEvent extends Event
{
    private $conference;
    private $remainingDays;

    public function __construct(Conference $conference, int $remainingDays)
    {
        $this->conference = $conference;
        $this->remainingDays = $remainingDays;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function getRemainingDays(): int
    {
        return $this->remainingDays;
    }

    public function buildAttachment(): array
    {
        $cfpAttachment = SlackNotifier::ATTACHMENT;
        $template = '🔊  CFP for %s is closing %s';
        $conferenceLink = sprintf('<%s|%s>', $this->conference->getCfpUrl(), $this->conference->getName());

        switch ($this->remainingDays) {
            case 30:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, 'in *'.$this->remainingDays.' days* ! 😀');
                break;
            case 20:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, 'in *'.$this->remainingDays.' days* ! 🙂');
                break;
            case 10:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, 'in *'.$this->remainingDays.' days* ! 😮');
                break;
            case 5:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, 'in *'.$this->remainingDays.' days* ! 😨');
                break;
            case 1:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, 'in *'.$this->remainingDays.' day* ! 😰');
                break;
            case 0:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, '*today* ! 😱');
                break;
        }

        if ($this->conference->getSubmits()->count() > 0) {
            $talksField = SlackNotifier::SHORT_FIELD;
            $talksField['title'] = 'Talks submitted by colleagues';
            $talksField['value'] = $this->conference->getSubmits()->count().'  📝';
            array_push($cfpAttachment['fields'], $talksField);
        }

        $actionsField = SlackNotifier::SHORT_FIELD;
        $actionsField['title'] = 'Submit a talk';
        $actionsField['value'] = sprintf('<%s|%s>', $this->conference->getCfpUrl(), 'Go to the CFP  👉');
        array_push($cfpAttachment['fields'], $actionsField);

        return $cfpAttachment;
    }
}
