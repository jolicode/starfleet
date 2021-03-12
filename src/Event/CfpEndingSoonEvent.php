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

    /** @return array<string,mixed> */
    public function buildAttachment(): array
    {
        $cfpAttachment = SlackNotifier::ATTACHMENT;
        $template = '🔊  CFP for %s (%s) is closing %s';
        if (null !== $this->conference->getSiteUrl()) {
            $conferenceLink = sprintf('<%s|%s>', $this->conference->getSiteUrl(), $this->conference->getName());
        } else {
            $conferenceLink = $this->conference->getName();
        }
        $countdown = 'in *'.$this->remainingDays.' day'.($this->remainingDays > 1 ? 's' : '').'* !';

        switch ($this->remainingDays) {
            case 30:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), $countdown.' 😀');
                break;
            case 20:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), $countdown.' 🙂');
                break;
            case 10:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), $countdown.' 😮');
                break;
            case 5:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), $countdown.' 😨');
                break;
            case 1:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), $countdown.' 😰');
                break;
            case 0:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $this->conference->getCity(), '*today* ! 😱');
                break;
        }

        if ($this->conference->getSubmits()->count() > 0) {
            $talksField = SlackNotifier::SHORT_FIELD;
            $talksField['title'] = 'Talks submitted by colleagues';
            $talksField['value'] = $this->conference->getSubmits()->count().'  📝';
            $cfpAttachment['fields'][] = $talksField;
        }

        $actionsField = SlackNotifier::SHORT_FIELD;
        $actionsField['title'] = 'Submit a talk';
        $actionsField['value'] = sprintf('<%s|%s>', $this->conference->getCfpUrl(), 'Go to the CFP  👉');
        $cfpAttachment['fields'][] = $actionsField;

        return $cfpAttachment;
    }
}
