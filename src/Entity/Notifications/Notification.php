<?php

namespace App\Entity\Notifications;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InheritanceType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\DiscriminatorColumn;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="trigger", type="string")
 * @DiscriminatorMap({
 *     "submit" = "SubmitNotification"
 * })
 */
abstract class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="Notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $targetUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTargetUser(): User
    {
        return $this->targetUser;
    }

    public function setTargetUser(User $targetUser): self
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }
}
