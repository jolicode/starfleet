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

class NewConferencesEvent extends Event
{
    /** @var array<Conference> */
    private array $newConferences;

    /** @param array<Conference> $newConferences */
    public function __construct(array $newConferences)
    {
        $this->newConferences = $newConferences;
    }

    /** @return array<Conference> */
    public function getNewConferences(): array
    {
        return $this->newConferences;
    }

    /** @return array<string,mixed> */
    public function buildAttachmentField(Conference $conference, RouterInterface $router): array
    {
        $conferenceField = SlackNotifier::SHORT_FIELD;
        $conferenceField['short'] = false;

        if (null !== $conference->getStartAt() && null !== $conference->getEndAt()) {
            if (null !== $conference->getCountry() && !$conference->isOnline()) {
                $conferenceField['title'] = 'From '.$conference->getStartAt()->format('d F Y').' to '.$conference->getEndAt()->format('d F Y').' at '.$conference->getCity().' ('.$conference->getCountry().')';
            } elseif ($conference->isOnline()) {
                $conferenceField['title'] = 'From '.$conference->getStartAt()->format('d F Y').' to '.$conference->getEndAt()->format('d F Y').' Online';
            } else {
                $conferenceField['title'] = 'From '.$conference->getStartAt()->format('d F Y').' to '.$conference->getEndAt()->format('d F Y').' at '.$conference->getCity();
            }
        }

        $starfleetLink = $router->generate('conferences_show', [
            'slug' => $conference->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $conferenceField['value'] = '<'.$starfleetLink.'|'.$conference->getName().'>';

        if (null !== $conference->getCfpUrl()) {
            $conferenceField['value'] .= ' - <'.$conference->getCfpUrl().'|CFP>';

            if (null !== $conference->getCfpEndAt()) {
                $conferenceField['value'] .= ' (fin le '.$conference->getCfpEndAt()->format('d F Y').')  ⏱';
            }
        }

        return $conferenceField;
    }
}
