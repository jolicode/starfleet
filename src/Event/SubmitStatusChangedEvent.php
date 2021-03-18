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
use Symfony\Contracts\EventDispatcher\Event;

class SubmitStatusChangedEvent extends Event
{
    private $submit;

    public function __construct(Submit $submit)
    {
        $this->submit = $submit;
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }
}
