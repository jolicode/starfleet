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

use App\Enum\Workflow\Transition\Participation as ParticipationTransitionEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipationRepository")
 */
class Participation
{
    use TimestampableEntity;

    public const TRANSPORT_STATUS_NOT_NEEDED = 'not_needed';
    public const TRANSPORT_STATUS_NEEDED = 'needed';
    public const TRANSPORT_STATUS_BOOKED = 'booked';

    public const HOTEL_STATUS_NOT_NEEDED = 'not_needed';
    public const HOTEL_STATUS_NEEDED = 'needed';
    public const HOTEL_STATUS_BOOKED = 'booked';

    public const CONFERENCE_TICKET_STATUS_NOT_NEEDED = 'not_needed';
    public const CONFERENCE_TICKET_STATUS_NEEDED = 'needed';
    public const CONFERENCE_TICKET_STATUS_BOOKED = 'booked';

    public const TRANSPORT_STATUSES = [
        self::TRANSPORT_STATUS_NOT_NEEDED => self::TRANSPORT_STATUS_NOT_NEEDED,
        self::TRANSPORT_STATUS_NEEDED => self::TRANSPORT_STATUS_NEEDED,
        self::TRANSPORT_STATUS_BOOKED => self::TRANSPORT_STATUS_BOOKED,
    ];

    public const HOTEL_STATUSES = [
        self::HOTEL_STATUS_NOT_NEEDED => self::HOTEL_STATUS_NOT_NEEDED,
        self::HOTEL_STATUS_NEEDED => self::HOTEL_STATUS_NEEDED,
        self::HOTEL_STATUS_BOOKED => self::HOTEL_STATUS_BOOKED,
    ];

    public const CONFERENCE_TICKET_STATUSES = [
        self::CONFERENCE_TICKET_STATUS_NOT_NEEDED => self::CONFERENCE_TICKET_STATUS_NOT_NEEDED,
        self::CONFERENCE_TICKET_STATUS_NEEDED => self::CONFERENCE_TICKET_STATUS_NEEDED,
        self::CONFERENCE_TICKET_STATUS_BOOKED => self::CONFERENCE_TICKET_STATUS_BOOKED,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="participations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private Conference $conference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private User $participant;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $asSpeaker;

    /**
     * @ORM\Column(type="string")
     */
    #[Assert\Choice(choices: [
        self::TRANSPORT_STATUS_NOT_NEEDED,
        self::TRANSPORT_STATUS_NEEDED,
        self::TRANSPORT_STATUS_BOOKED,
    ])]
    private string $transportStatus = self::TRANSPORT_STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    #[Assert\Choice(choices: [
        self::HOTEL_STATUS_NOT_NEEDED,
        self::HOTEL_STATUS_NEEDED,
        self::HOTEL_STATUS_BOOKED,
    ])]
    private string $hotelStatus = self::HOTEL_STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    #[Assert\Choice(choices: [
        self::CONFERENCE_TICKET_STATUS_NOT_NEEDED,
        self::CONFERENCE_TICKET_STATUS_NEEDED,
        self::CONFERENCE_TICKET_STATUS_BOOKED,
    ])]
    private string $conferenceTicketStatus = self::CONFERENCE_TICKET_STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    private string $marking;

    public function __construct()
    {
        $this->marking = ParticipationTransitionEnum::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(User $participant): self
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

    public function getTransportStatus(): string
    {
        return $this->transportStatus;
    }

    public function setTransportStatus(string $transportStatus): self
    {
        $this->transportStatus = $transportStatus;

        return $this;
    }

    public function getHotelStatus(): string
    {
        return $this->hotelStatus;
    }

    public function setHotelStatus(string $hotelStatus): self
    {
        $this->hotelStatus = $hotelStatus;

        return $this;
    }

    public function getConferenceTicketStatus(): string
    {
        return $this->conferenceTicketStatus;
    }

    public function setConferenceTicketStatus(string $conferenceTicketStatus): self
    {
        $this->conferenceTicketStatus = $conferenceTicketStatus;

        return $this;
    }

    public function getMarking(): string
    {
        return $this->marking;
    }

    public function setMarking(string $marking): self
    {
        $this->marking = $marking;

        return $this;
    }
}
