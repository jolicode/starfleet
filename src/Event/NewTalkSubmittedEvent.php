<?php

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
