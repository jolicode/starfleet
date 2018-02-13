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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User implements UserInterface, \Serializable
{
    use TimestampableEntity;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="bio", type="text", nullable=true)
     */
    private $bio;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Submit", mappedBy="users")
     */
    private $submits;

    public function __construct()
    {
        $this->submits = new ArrayCollection();
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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

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

    public function __toString(): string
    {
        return $this->getName() ?? (string) $this->id;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->email,
            $this->googleId,
        ]);
    }

    public function unserialize($serialized): ?array
    {
        return list(
            $this->id,
            $this->email,
            $this->googleId) = unserialize($serialized);
    }
}
