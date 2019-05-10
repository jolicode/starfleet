<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Enum\Workflow\Place;

use MyCLabs\Enum\Enum;

class Participation extends Enum
{
    const PENDING = 'pending';

    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';

    const TICKET_BOUGHT = 'ticket_bought';
    const TRANSPORT_RESERVED = 'transport_reserved';
    const HOTEL_BOOKED = 'hotel_booked';

    const VALIDATED = 'validated';

    const CANCELLED = 'cancelled';
}
