<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Event\Notification;

use App\Entity\Participation;
use Symfony\Contracts\EventDispatcher\Event;

class ParticipationStatusChangedEvent extends Event
{
    public function __construct(
        private Participation $participation,
    ) {
    }

    public function getParticipation(): Participation
    {
        return $this->participation;
    }
}
