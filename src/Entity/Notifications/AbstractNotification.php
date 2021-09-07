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

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({
 *      "NewSubmit" = "NewSubmitNotification",
 *      "submitStatusChanged" = "SubmitStatusChangedNotification",
 *      "participationStatusChangedNotification" = "ParticipationStatusChangedNotification",
 *      "newFeaturedConference" = "NewFeaturedConferenceNotification"
 * })
 */
abstract class AbstractNotification
{
    // These const must match the names of the templates in templates/user/notifications
    public const TRIGGER_NEW_SUBMIT = 'new_submit';
    public const TRIGGER_SUBMIT_STATUS_CHANGED = 'submit_status_changed';
    public const TRIGGER_PARTICIPATION_STATUS_CHANGED = 'participation_status_changed';
    public const TRIGGER_NEW_FEATURED_CONFERENCE = 'new_featured_conference';

    public const TRIGGERS = [
        self::TRIGGER_NEW_SUBMIT,
        self::TRIGGER_SUBMIT_STATUS_CHANGED,
        self::TRIGGER_PARTICIPATION_STATUS_CHANGED,
        self::TRIGGER_NEW_FEATURED_CONFERENCE,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected User $targetUser;

    /**
     * @ORM\Column(type="datetime")
     */
    protected \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\Choice(choices: self::TRIGGERS)]
    protected string $trigger;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $read = false;

    public function __construct(User $targetUser, string $trigger)
    {
        $this->createdAt = new \DateTime();
        $this->targetUser = $targetUser;
        $targetUser->addNotification($this);
        $this->trigger = $trigger;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTargetUser(): User
    {
        return $this->targetUser;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function markAsRead(): void
    {
        $this->read = true;
    }
}
