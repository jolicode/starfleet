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

use App\Enum\Workflow\Place\Participation as ParticipationPlace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipationRepository")
 */
class Participation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participant;

    /**
     * @ORM\Column(type="boolean")
     */
    private $asSpeaker;

    /**
     * @ORM\Column(type="boolean")
     */
    private $needTransport = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $needHotel = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $needTicket = true;

    /**
     * @ORM\Column(type="json_array")
     */
    private $marking = [];

    public function __construct()
    {
        $this->marking = [ParticipationPlace::PENDING => 1];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function getAsSpeaker(): ?bool
    {
        return $this->asSpeaker;
    }

    public function setAsSpeaker(bool $asSpeaker): self
    {
        $this->asSpeaker = $asSpeaker;

        return $this;
    }

    public function getNeedTransport(): ?bool
    {
        return $this->needTransport;
    }

    public function setNeedTransport(bool $needTransport): self
    {
        $this->needTransport = $needTransport;

        return $this;
    }

    public function getNeedHotel(): ?bool
    {
        return $this->needHotel;
    }

    public function setNeedHotel(bool $needHotel): self
    {
        $this->needHotel = $needHotel;

        return $this;
    }

    public function getNeedTicket(): ?bool
    {
        return $this->needTicket;
    }

    public function setNeedTicket(bool $needTicket): self
    {
        $this->needTicket = $needTicket;

        return $this;
    }

    public function getMarking(): array
    {
        return $this->marking;
    }

    public function setMarking(array $marking): self
    {
        $this->marking = $marking;

        return $this;
    }
}
