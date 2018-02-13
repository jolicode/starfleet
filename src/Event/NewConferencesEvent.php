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
use Symfony\Component\Routing\RouterInterface;

class NewConferencesEvent extends Event
{
    private $router;
    private $newConferences;

    public function __construct(array $newConferences, RouterInterface $router)
    {
        $this->newConferences = $newConferences;
        $this->router = $router;
    }

    public function getNewConferences(): array
    {
        return $this->newConferences;
    }

    public function buildAttachmentField(Conference $conference): array
    {
        $conferenceField = SlackNotifier::SHORT_FIELD;
        $conferenceField['short'] = false;
        $conferenceField['title'] = 'From '.$conference->getStartAt()->format('d F Y').' to '.$conference->getEndAt()->format('d F Y').' at '.$conference->getLocation();

        $starfleetLink = $this->router->generate('conferences_show', [
            'slug' => $this->conference->getSlug(),
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
