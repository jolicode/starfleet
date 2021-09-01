<?php

namespace App\Entity\Notifications;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=App\Repository\NotificationRepository::class)
 */
class Notification
{
    public const TRIGGER_SUBMIT_ADDED = 'SubmitAdded';
    public const TRIGGER_SUBMIT_STATUS_CHANGED = 'SubmitStatusChanged';
    public const TRIGGER_PARTICIPATION_STATUS_CHANGED = 'ParticipationStatusChanged';
    public const TRIGGER_NEW_FEATURED_CONFERENCE = 'NewFeaturedConference';

    public const TRIGGERS = [
        self::TRIGGER_SUBMIT_ADDED,
        self::TRIGGER_SUBMIT_STATUS_CHANGED,
        self::TRIGGER_PARTICIPATION_STATUS_CHANGED,
        self::TRIGGER_NEW_FEATURED_CONFERENCE,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
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
     *
     * An array of data that will oftenly contain serialized data, but also various other data.
     */
    private array $data = [
        'objects' => [],
        'strings' => [],
    ];

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

    public function addSerializedObject(string $className, string $serializedObject)
    {
        $this->data['objects'][$className] = $serializedObject;
    }

    public function addString(string $key, string $value)
    {
        $this->data['strings'][$key] = $value;
    }
}
