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
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
    use TimestampableEntity;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Conference", mappedBy="tags")
     *
     * @var Collection<Conference>
     */
    private $conferences;

    /**
     * @ORM\Column(name="selected", type="boolean")
     */
    private $selected = false;

    public function __construct()
    {
        $this->conferences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function addConference(Conference $conference): self
    {
        $this->conferences[] = $conference;

        return $this;
    }

    public function removeConference(Conference $conference): self
    {
        $this->conferences->removeElement($conference);

        return $this;
    }

    public function getConferences(): ?Collection
    {
        return $this->conferences;
    }

    public function __toString(): string
    {
        return $this->getName() ?? (string) $this->id;
    }

    public function setSelected(bool $selected)
    {
        $this->selected = $selected;
    }

    public function isSelected()
    {
        return $this->selected;
    }
}
