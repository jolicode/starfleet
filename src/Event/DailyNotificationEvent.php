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
    /**
     * @param array<Conference>            $newConferences
     * @param array<int,array<Conference>> $endingCfps
     * */
    public function __construct(
        private array $newConferences,
        private array $endingCfps,
    ) {
    }

    /** @return array<Conference> */
    public function getNewConferences(): array
    {
        return $this->newConferences;
    }

    /** @return array<int,array<Conference>> */
    public function getEndingCfps(): array
    {
        return $this->endingCfps;
    }
}
