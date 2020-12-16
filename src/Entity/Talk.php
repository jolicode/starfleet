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
 * @ORM\Table(name="talk")
 * @ORM\Entity()
 */
class Talk
{
    use TimestampableEntity;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="intro", type="text")
     */
    private $intro;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Submit", mappedBy="talk", cascade={"persist", "remove"})
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

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function getSubmits(): ?Collection
    {
        return $this->submits;
    }

    public function addSubmit(Submit $submit): self
    {
        $this->submits[] = $submit;

        return $this;
    }

    public function removeSubmit(Submit $submit): self
    {
        if ($this->submits->contains($submit)) {
            $this->submits->removeElement($submit);
        }

        return $this;
    }

    public function getUniqueUsers(): array
    {
        $uniqueNames = [];
        foreach ($this->getSubmits() as $submit) {
            $usersNames = [];
            foreach ($submit->getUsers() as $user) {
                $usersNames[] = $user->getName();
            }

            sort($usersNames);
            $uniqueNames[] = $usersNames;
        }
        $uniqueNames = array_unique($uniqueNames, SORT_REGULAR);
        sort($uniqueNames);

        return $uniqueNames;
    }

    public function __toString(): string
    {
        return $this->getTitle() ?? (string) $this->id;
    }
}
