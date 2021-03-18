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
}
