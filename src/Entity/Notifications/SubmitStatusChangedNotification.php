<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Entity\Notifications;

use App\Entity\Submit;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SubmitStatusChangedNotification extends AbstractNotification
{
    /**
     * @ORM\ManyToOne(targetEntity=Submit::class)
     * @ORM\JoinColumn()
     */
    private ?Submit $submit;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn()
     */
    private ?User $emitter;

    public function __construct(Submit $submit, User $emitter, User $targetUser, string $trigger)
    {
        $this->submit = $submit;
        $this->emitter = $emitter;
        parent::__construct($targetUser, $trigger);
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function getEmitter(): User
    {
        return $this->emitter;
    }
}
