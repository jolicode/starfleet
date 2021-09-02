<?php

namespace App\Entity\Notifications;

use App\Entity\Participation;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ParticipationStatusChangedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity=Participation::class)
     * @ORM\JoinColumn
     */
    private ?Participation $participation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn
     */
    private ?User $emitter;

    public function __construct(Participation $participation, User $targetUser)
    {
        $this->participation = $participation;
        parent::__construct($targetUser);
    }

    public function getParticipation(): Participation
    {
        return $this->participation;
    }

    public function getEmitter(): User
    {
        return $this->emitter;
    }

    public function setEmitter(User $emitter): self
    {
        $this->emitter = $emitter;

        return $this;
    }
}
