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
use Symfony\Contracts\EventDispatcher\Event;

class NewTalkSubmittedEvent extends Event
{
    /** @param array<Submit> $submits */
    public function __construct(
        private Talk $talk,
        private array $submits,
    ) {
    }

    public function getTalk(): Talk
    {
        return $this->talk;
    }

    /** @return array<Submit> */
    public function getSubmits(): array
    {
        return $this->submits;
    }
}
