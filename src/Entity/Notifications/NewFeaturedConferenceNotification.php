<?php

namespace App\Entity\Notifications;

use App\Entity\Conference;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class NewFeaturedConferenceNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity=Conference::class)
     * @ORM\JoinColumn
     */
    private ?Conference $conference;

    public function __construct(Conference $conference, User $targetUser)
    {
        $this->conference = $conference;
        parent::__construct($targetUser);
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }
}
