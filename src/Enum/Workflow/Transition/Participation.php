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
    const ACCEPT_FROM_CANCELLED = 'accept_from_cancelled';
    const ACCEPT_TRANSITIONS = [
        self::ACCEPT,
        self::ACCEPT_FROM_CANCELLED,
    ];

    const REJECT = 'reject';
    const REJECT_FROM_ACCEPTED = 'reject_from_accepted';
    const REJECT_TRANSITIONS = [
        self::REJECT,
        self::REJECT_FROM_ACCEPTED,
    ];

    const BUY_TICKET = 'buy_ticket';
    const RESERVE_TRANSPORT = 'reserve_transport';
    const BOOK_HOTEL = 'book_hotel';

    const VALIDATE = 'validate';

    const CANCEL = 'cancel';
    const CANCEL_FROM_REJECTED = 'cancel_from_rejected';
    const CANCEL_FROM_VALIDATED = 'cancel_from_validated';
    const CANCEL_TRANSITIONS = [
        self::CANCEL,
        self::CANCEL_FROM_REJECTED,
        self::CANCEL_FROM_VALIDATED,
    ];
}
