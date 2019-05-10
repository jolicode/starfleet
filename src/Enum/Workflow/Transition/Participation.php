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
    const ACCEPT = 'accept';
    const REJECT = 'reject';

    const BUY_TICKET = 'buy_ticket';
    const RESERVE_TRANSPORT = 'reserve_transport';
    const BOOK_HOTEL = 'book_hotel';

    const VALIDATE = 'validate';
    const CANCEL = 'cancel';
}
