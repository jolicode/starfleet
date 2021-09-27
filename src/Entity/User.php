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

use App\Entity\Notifications\AbstractNotification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity()
 */
class User implements UserInterface, \Serializable, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private ?string $googleId = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $githubId = null;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email;

    /**
     * @ORM\Column(name="job", type="string", length=255, nullable=true)
     */
    private ?string $job = null;

    /**
     * @ORM\Column(name="twitter_account", type="string", length=255, nullable=true)
     */
    private ?string $twitterAccount = null;

    /**
     * @ORM\Column(name="tshirt_size", type="string", length=10, nullable=true)
     */
    private ?string $tshirtSize = null;

    /**
     * @ORM\Column(name="bio", type="text", nullable=true)
     */
    private ?string $bio = null;

    /**
     * @ORM\Column(name="food_preferences", type="text", nullable=true)
     */
    private ?string $foodPreferences = null;

    /**
     * @ORM\Column(name="allergies", type="text", nullable=true)
     */
    private ?string $allergies = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Submit", mappedBy="users")
     *
     * @var Collection<Submit>
     */
    private Collection $submits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participation", mappedBy="participant", cascade={"persist", "remove"})
     *
     * @var Collection<Participation>
     */
    private Collection $participations;

    /**
     * @ORM\Column(type="json")
     *
     * @var array<string>
     */
    private array $roles;

    /**
     * @Assert\Length(max=4096)
     */
    protected string $plainPassword;

    /**
     * @Assert\Length(max=4096)
     */
    protected ?string $salt = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $password = null;

    /**
     * @ORM\OneToMany(targetEntity=AbstractNotification::class, mappedBy="targetUser", cascade={"persist"})
     *
     * @var Collection<AbstractNotification>
     */
    private Collection $notifications;

    public function __construct()
    {
        $this->submits = new ArrayCollection();
        $this->participations = new ArrayCollection();
        // guarantee every user at least has ROLE_USER
        $this->roles = ['ROLE_USER'];
        $this->notifications = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function getGithubId(): ?string
    {
        return $this->githubId;
    }

    public function setGithubId(?string $githubId): self
    {
        $this->githubId = $githubId;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setJob(?string $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setTwitterAccount(?string $twitterAccount): self
    {
        $this->twitterAccount = $twitterAccount;

        return $this;
    }

    public function getTwitterAccount(): ?string
    {
        return $this->twitterAccount;
    }

    public function setTshirtSize(?string $tshirtSize): self
    {
        $this->tshirtSize = $tshirtSize;

        return $this;
    }

    public function getTshirtSize(): ?string
    {
        return $this->tshirtSize;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setFoodPreferences(?string $foodPreferences): self
    {
        $this->foodPreferences = $foodPreferences;

        return $this;
    }

    public function getFoodPreferences(): ?string
    {
        return $this->foodPreferences;
    }

    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!\in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function addSubmit(Submit $submit): self
    {
        $this->submits[] = $submit;

        return $this;
    }

    public function removeSubmit(Submit $submit): self
    {
        $this->submits->removeElement($submit);

        return $this;
    }

    public function getSubmits(): ?Collection
    {
        return $this->submits;
    }

    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations[] = $participation;
            $participation->setParticipant($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->contains($participation)) {
            $this->participations->removeElement($participation);
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = '';
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->getId(),
            $this->getUserIdentifier(),
            $this->getPassword(),
            $this->getSalt(),
            $this->getGoogleId(),
            $this->getGithubId(),
        ]);
    }

    /** @return array<string> */
    public function unserialize($serialized): ?array
    {
        return list(
            $this->id,
            $this->email,
            $this->password,
            $this->salt,
            $this->googleId,
            $this->githubId
        ) = unserialize($serialized);
    }

    /**
     * @return Collection|AbstractNotification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(AbstractNotification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
        }

        return $this;
    }
}
