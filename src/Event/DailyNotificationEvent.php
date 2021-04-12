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
use Symfony\Contracts\EventDispatcher\Event;

class DailyNotificationEvent extends Event
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
}
