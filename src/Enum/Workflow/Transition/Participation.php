<?php

namespace App\Enum\Workflow\Transition;

use MyCLabs\Enum\Enum;

class Participation extends Enum
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';
}
