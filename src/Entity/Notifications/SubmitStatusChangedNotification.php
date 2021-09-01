<?php

namespace App\Entity\Notifications;

use App\Entity\Submit;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Notifications\Notification;

/**
 * @ORM\Entity(repositoryClass=SubmitStatusChangedNotificationRepository::class)
 */
class SubmitStatusChangedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity=Submit::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Submit $submit;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $newStatus;

    public function getSubmit(): Submit
    {
        return $this->submit;
    }

    public function setSubmit(Submit $submit): self
    {
        $this->submit = $submit;

        return $this;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function setNewStatus(string $newStatus): self
    {
        $this->newStatus = $newStatus;

        return $this;
    }
}
