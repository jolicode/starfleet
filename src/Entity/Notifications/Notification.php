<?php

namespace App\Entity\Notifications;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NotificationRepository;
use App\Event\Notification\SubmitAddedEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    public const TRIGGER_SUBMIT_ADDED = 'SubmitAdded';
    public const TRIGGER_SUBMIT_STATUS_CHANGED = 'SubmitStatusChanged';

    public const TRIGGERS = [
        SubmitAddedEvent::class => self::TRIGGER_SUBMIT_ADDED,
        SubmitStatusChangedEvent::class => self::TRIGGER_SUBMIT_STATUS_CHANGED,
    ];

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

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\Choice(choices: self::TRIGGERS)]
    private string $trigger;

    /**
     * @ORM\Column(type="jsonb")
     */
    private array $data;

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

    public function getTrigger(): ?string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }

    /** @return array<mixed> */
    public function getData(): array
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
