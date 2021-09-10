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

use App\Entity\Participation;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ParticipationStatusChangedNotification extends AbstractNotification
{
    /**
     * @ORM\ManyToOne(targetEntity=Participation::class)
     * @ORM\JoinColumn()
     */
    private ?Participation $participation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn()
     */
    private ?User $emitter;

    public function __construct(Participation $participation, User $emitter, User $targetUser, string $trigger)
    {
        $this->participation = $participation;
        $this->emitter = $emitter;
        parent::__construct($targetUser, $trigger);
    }

    public function getParticipation(): Participation
    {
        return $this->participation;
    }

    public function getEmitter(): User
    {
        return $this->emitter;
    }
}
