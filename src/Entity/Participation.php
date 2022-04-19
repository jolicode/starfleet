<?php

namespace App\Entity;

use App\Validator\Constraints as CustomAssert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 *
 * @CustomAssert\NoParticipationDuplicate()
 */
class Participation
{
    use TimestampableEntity;

    public const STATUS_NOT_NEEDED = 'not_needed';
    public const STATUS_NEEDED = 'needed';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_PENDING = 'pending';

    public const STATUSES = [
        self::STATUS_NOT_NEEDED => self::STATUS_NOT_NEEDED,
        self::STATUS_NEEDED => self::STATUS_NEEDED,
        self::STATUS_BOOKED => self::STATUS_BOOKED,
    ];

    /**
     * @ORM\Id()
     *
     * @ORM\GeneratedValue()
     *
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="participations", cascade={"persist"})
     *
     * @ORM\JoinColumn(nullable=false)
     *
     * @CustomAssert\NotEndedConference()
     */
    private Conference $conference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participations", cascade={"persist"})
     *
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
        self::STATUS_NOT_NEEDED,
        self::STATUS_NEEDED,
        self::STATUS_BOOKED,
    ])]
    private string $transportStatus = self::STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    #[Assert\Choice(choices: [
        self::STATUS_NOT_NEEDED,
        self::STATUS_NEEDED,
        self::STATUS_BOOKED,
    ])]
    private string $hotelStatus = self::STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    #[Assert\Choice(choices: [
        self::STATUS_NOT_NEEDED,
        self::STATUS_NEEDED,
        self::STATUS_BOOKED,
    ])]
    private string $conferenceTicketStatus = self::STATUS_NOT_NEEDED;

    /**
     * @ORM\Column(type="string")
     */
    private string $marking = self::STATUS_PENDING;

    public function __construct(User $participant)
    {
        $this->participant = $participant;
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
