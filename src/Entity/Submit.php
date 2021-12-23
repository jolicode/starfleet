<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Entity;

use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="submit")
 * @ORM\Entity()
 *
 * @CustomAssert\NoSubmitDuplicate()
 */
class Submit
{
    use TimestampableEntity;

    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_ACCEPTED => self::STATUS_ACCEPTED,
        self::STATUS_REJECTED => self::STATUS_REJECTED,
        self::STATUS_PENDING => self::STATUS_PENDING,
        self::STATUS_DONE => self::STATUS_DONE,
    ];

    public const STATUS_EMOJIS = [
        self::STATUS_PENDING => 'En attente  🤞',
        self::STATUS_ACCEPTED => 'Accepté  🎉',
        self::STATUS_REJECTED => 'Refusé  😢',
        self::STATUS_DONE => 'Donné  ✅',
    ];

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="submitted_at", type="date")
     */
    private \DateTimeInterface $submittedAt;

    /**
     * @ORM\Column(name="status", type="string", length=255)
     */
    private string $status = self::STATUS_PENDING;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="submits")
     * @ORM\JoinTable(name="submits_users")
     *
     * @CustomAssert\MustBeByUser(groups={"user_account"})
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must add at least one user"
     * )
     *
     *  @var Collection<User>
     */
    private Collection $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="submits")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @CustomAssert\NotEndedConference()
     */
    private ?Conference $conference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Talk", inversedBy="submits", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Talk $talk;

    private bool $statusChanged = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private ?User $submittedBy;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->submittedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return ($this->getTalk()->getTitle() && $this->getConference()->getName())
            ? sprintf('%s - %s', $this->getTalk()->getTitle(), $this->getConference()->getName())
            : (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSubmittedAt(\DateTime $submittedAt): self
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->submittedAt;
    }

    public function setStatus(string $status): self
    {
        if ($this->status !== $status) {
            $this->statusChanged = true;
        }
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return Collection<User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function setConference(Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function setTalk(Talk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }

    public function getTalk(): Talk
    {
        return $this->talk;
    }

    public function getSpeakersNames(): string
    {
        return array_reduce($this->getUsers()->toArray(), function ($r, User $u) {
            return '' === $r ? $u->getName() : $r . ', ' . $u->getName();
        }, '');
    }

    public function hasStatusChanged(): bool
    {
        return $this->statusChanged;
    }

    public function resetStatusChanged(): void
    {
        $this->statusChanged = false;
    }

    public function getSubmittedBy(): ?User
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(User $submittedBy): self
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }
}
