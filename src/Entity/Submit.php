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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="submit")
 * @ORM\Entity()
 */
class Submit
{
    use TimestampableEntity;

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';

    const STATUSES = [
        self::STATUS_ACCEPTED => self::STATUS_ACCEPTED,
        self::STATUS_REJECTED => self::STATUS_REJECTED,
        self::STATUS_PENDING => self::STATUS_PENDING,
        self::STATUS_DONE => self::STATUS_DONE,
    ];

    const STATUS_EMOJIS = [
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
    private string $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="submits")
     * @ORM\JoinTable(name="submits_users")
     *
     * @var Collection<User>
     */
    private Collection $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="submits", cascade={"persist"})
     */
    private ?Conference $conference = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Talk", inversedBy="submits")
     */
    private ?Talk $talk = null;

    private bool $statusChanged = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSubmittedAt(?\DateTime $submittedAt): self
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
        $this->status = $status;
        $this->statusChanged = true;

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
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setTalk(?Talk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }

    public function getTalk(): ?Talk
    {
        return $this->talk;
    }

    public function __toString(): string
    {
        return $this->getTalk() && $this->getConference()
            ? sprintf('%s - %s', $this->getTalk()->getTitle(), $this->getConference()->getName())
            : (string) $this->id;
    }

    public function reduceSpeakersNames(): string
    {
        return array_reduce($this->getUsers()->toArray(), function ($r, User $u) {
            return '' === $r ? $u->getName() : $r.', '.$u->getName();
        }, '');
    }

    public function getStatusChanged(): bool
    {
        return $this->statusChanged;
    }

    public function resetStatusChanged(): void
    {
        $this->statusChanged = false;
    }
}
