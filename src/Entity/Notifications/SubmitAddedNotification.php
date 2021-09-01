<?php

namespace App\Entity\Notifications;

use App\Entity\Talk;
use App\Entity\User;
use App\Entity\Conference;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Notifications\Notification;

/**
 * @ORM\Entity(repositoryClass=NewSubmitWithAnotherUserNotificationRepository::class)
 */
class SubmitAddedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private User $sourceUser;

    /**
     * @ORM\ManyToOne(targetEntity=Conference::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Conference $conference;

    /**
     * @ORM\ManyToOne(targetEntity=Talk::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Talk $talk;

    public function getSourceUser(): ?User
    {
        return $this->sourceUser;
    }

    public function setSourceUser(?User $sourceUser): self
    {
        $this->sourceUser = $sourceUser;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getTalk(): ?Talk
    {
        return $this->talk;
    }

    public function setTalk(?Talk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }
}
