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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\Event;

class NewConferenceEvent extends Event
{
    private $conference;

    public function __construct(Conference $conference)
    {
        $this->conference = $conference;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    /** @return array<string,mixed> */
    public function buildAttachment(RouterInterface $router): array
    {
        $conferenceAttachment = SlackNotifier::ATTACHMENT;
        $conferenceAttachment['pretext'] = '✨  *New conference added*';
        $conferenceAttachment['title'] = $this->conference->getName();
        $conferenceAttachment['title_link'] = $this->conference->getSiteUrl();

        if (null !== $this->conference->getCfpUrl()) {
            $cfpField = SlackNotifier::LONG_FIELD;
            if (null !== $this->conference->getCfpEndAt()) {
                $cfpField['title'] = 'CFP open until '.$this->conference->getCfpEndAt()->format('d F Y').'  ⏱';
            } else {
                $cfpField['title'] = 'CFP';
            }
            $cfpField['value'] = '<'.$this->conference->getCfpUrl().'|Submit a talk>  👉';

            $conferenceAttachment['fields'][] = $cfpField;
        }

        $startDateField = SlackNotifier::SHORT_FIELD;
        $startDateField['title'] = 'From  🕑';
        $startDateField['value'] = $this->conference->getStartAt()->format('d F Y');
        $conferenceAttachment['fields'][] = $startDateField;

        $endDateField = SlackNotifier::SHORT_FIELD;
        $endDateField['title'] = 'To  🕣';
        if (null !== $this->conference->getEndAt()) {
            $endDateField['value'] = $this->conference->getEndAt()->format('d F Y');
        } else {
            $endDateField['value'] = 'Unknown';
        }
        $conferenceAttachment['fields'][] = $endDateField;

        if ($this->conference->isOnline()) {
            $cityField = SlackNotifier::SHORT_FIELD;
            $cityField['title'] = 'Online Conference 🖥️';
            $cityField['value'] = '127.0.0.1';
            $conferenceAttachment['fields'][] = $cityField;
        } else {
            $cityField = SlackNotifier::SHORT_FIELD;
            $cityField['title'] = 'City  🏙️';
            $cityField['value'] = $this->conference->getCity();
            $conferenceAttachment['fields'][] = $cityField;

            $countryField = SlackNotifier::SHORT_FIELD;
            $countryField['title'] = 'Country  🗺';
            $countryField['value'] = $this->conference->getCountry();
            $conferenceAttachment['fields'][] = $countryField;
        }

        if ($this->conference->getParticipations()->count() > 0) {
            $starfleetLinkField = SlackNotifier::SHORT_FIELD;
            $starfleetLinkField['title'] = 'Starfleet link  🚀';
            $starfleetLinkField['value'] = $router->generate('conferences_show', [
                'slug' => $this->conference->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $conferenceAttachment['fields'][] = $starfleetLinkField;
        }

        $payload['attachments'] = [
            $conferenceAttachment,
        ];

        return $conferenceAttachment;
    }
}
