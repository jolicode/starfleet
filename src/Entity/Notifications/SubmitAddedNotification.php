<?php

namespace App\Entity\Notifications;

use App\Entity\Submit;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SubmitAddedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity=Submit::class)
     * @ORM\JoinColumn
     */
    private ?Submit $submit;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn
     */
    private ?User $emitter;

    public function __construct(Submit $submit, User $targetUser)
    {
        $this->submit = $submit;
        parent::__construct($targetUser);
    }

    public function getSubmit(): Submit
    {
        return $this->submit;
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
