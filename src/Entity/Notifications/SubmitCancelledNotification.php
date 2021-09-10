<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Entity\Notifications;

use App\Entity\Conference;
use App\Entity\Talk;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SubmitCancelledNotification extends AbstractNotification
{
    /**
     * @ORM\ManyToOne(targetEntity=Talk::class)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Talk $talk;

    /**
     * @ORM\ManyToOne(targetEntity=Conference::class)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Conference $conference;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?User $emitter;

    public function __construct(Talk $talk, Conference $conference, User $emitter, User $targetUser, string $trigger)
    {
        $this->talk = $talk;
        $this->conference = $conference;
        $this->emitter = $emitter;
        parent::__construct($targetUser, $trigger);
    }

    public function getTalk(): Talk
    {
        return $this->talk;
    }

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function getEmitter(): User
    {
        return $this->emitter;
    }
}
