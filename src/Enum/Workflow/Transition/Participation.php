<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Enum\Workflow\Transition;

use MyCLabs\Enum\Enum;

class Participation extends Enum
{
    public const PENDING = 'pending';
    public const ACCEPT = 'accept';
    public const REJECT = 'reject';
    public const CANCEL = 'cancel';
}
