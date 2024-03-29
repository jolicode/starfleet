<?php

namespace App\Entity;

use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="talk")
 *
 * @ORM\Entity()
 */
class Talk
{
    use TimestampableEntity;

    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="title", type="string", length=255, unique="true")
     *
     * @CustomAssert\UniqueTitle()
     */
    private string $title;

    /**
     * @ORM\Column(name="intro", type="text")
     */
    private string $intro;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Submit", mappedBy="talk", cascade={"persist", "remove"})
     *
     * @var Collection<Submit>
     */
    private Collection $submits;

    public function __construct()
    {
        $this->submits = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    /** @return Collection<Submit> */
    public function getSubmits(): Collection
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

    /** @return array<array<string>> */
    public function getUniqueUsersNames(): array
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
        $uniqueNames = array_unique($uniqueNames, \SORT_REGULAR);
        sort($uniqueNames);

        return $uniqueNames;
    }
}
