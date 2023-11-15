<?php

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
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Submit $submit;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
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
