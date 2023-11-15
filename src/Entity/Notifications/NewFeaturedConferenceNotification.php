<?php

namespace App\Entity\Notifications;

use App\Entity\Conference;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class NewFeaturedConferenceNotification extends AbstractNotification
{
    /**
     * @ORM\ManyToOne(targetEntity=Conference::class)
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Conference $conference;

    public function __construct(Conference $conference, User $targetUser, string $trigger)
    {
        $this->conference = $conference;
        parent::__construct($targetUser, $trigger);
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }
}
