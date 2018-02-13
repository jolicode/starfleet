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
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class NewConferenceEvent extends Event
{
    private $conference;
    private $router;

    public function __construct(Conference $conference, RouterInterface $router)
    {
        $this->conference = $conference;
        $this->router = $router;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function buildAttachment(): array
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

            array_push($conferenceAttachment['fields'], $cfpField);
        }

        $startDateField = SlackNotifier::SHORT_FIELD;
        $startDateField['title'] = 'From  🕑';
        $startDateField['value'] = $this->conference->getStartAt()->format('d F Y');
        array_push($conferenceAttachment['fields'], $startDateField);

        $endDateField = SlackNotifier::SHORT_FIELD;
        $endDateField['title'] = 'To  🕣';
        $endDateField['value'] = $this->conference->getEndAt()->format('d F Y');
        array_push($conferenceAttachment['fields'], $endDateField);

        $locationField = SlackNotifier::SHORT_FIELD;
        $locationField['title'] = 'Location  🗺';
        $locationField['value'] = $this->conference->getLocation();
        array_push($conferenceAttachment['fields'], $locationField);

        $starfleetLinkField = SlackNotifier::SHORT_FIELD;
        $starfleetLinkField['title'] = 'Starfleet link  🚀';
        $starfleetLinkField['value'] = $this->router->generate('conferences_show', [
            'slug' => $this->conference->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        array_push($conferenceAttachment['fields'], $starfleetLinkField);

        $payload['attachments'] = [
            $conferenceAttachment,
        ];

        return $conferenceAttachment;
    }
}
