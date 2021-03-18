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
    private Talk $talk;
    /** @var array<Submit> */
    private array $submits;

    /** @param array<Submit> $submits */
    public function __construct(Talk $talk, array $submits)
    {
        $this->talk = $talk;
        $this->submits = $submits;
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
