<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\EventListener\SlackNotifierEventListener;
use Doctrine\Persistence\ObjectManager;

class DevelopmentFixtures extends AbstractFixtures
{
    private $slackNotifierEventListener;

    public function __construct(SlackNotifierEventListener $slackNotifierEventListener)
    {
        $this->slackNotifierEventListener = $slackNotifierEventListener;
    }

    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $this->slackNotifierEventListener->disable();

        $admin = $this->addUser([
            'name' => 'Admin',
            'email' => 'admin@starfleet.app',
            'roles' => ['ROLE_ADMIN'],
            'password' => 'password',
        ]);

        $user1 = $this->addUser([
            'name' => 'User1',
            'email' => 'user1@starfleet.app',
            'roles' => ['ROLE_USER'],
            'password' => 'password',
        ]);

        $user2 = $this->addUser([
            'name' => 'User2',
            'email' => 'user2@starfleet.app',
            'roles' => ['ROLE_USER'],
            'password' => 'password',
        ]);

        $onlineConference = $this->addConference([
            'name' => 'Online Conf',
            'siteUrl' => 'https://online-conf.test',
            'cfpUrl' => 'https://cfp.online-conf.test',
            'startAt' => new \DateTime('+ 15 days'),
            'endAt' => new \DateTime('+ 16 days'),
            'cfpEndAt' => new \DateTime('+ 8 days'),
            'online' => true,
        ]);

        $onlineParticipation = $this->addParticipation([
            'participant' => $user1,
            'conference' => $onlineConference,
            'asSpeaker' => true,
            'marking' => ['validated' => 1],
        ]);
        $onlineConference->addParticipation($onlineParticipation);

        $nextConference = $this->addConference([
            'name' => 'Next Conf',
            'siteUrl' => 'https://next-conf.test',
            'cfpUrl' => 'https://cfp.next-conf.test',
            'startAt' => new \DateTime('+ 10 days'),
            'endAt' => new \DateTime('+ 11 days'),
            'cfpEndAt' => new \DateTime('+ 3 days'),
        ]);

        $nextParticipation = $this->addParticipation([
            'participant' => $user1,
            'conference' => $nextConference,
            'asSpeaker' => true,
            'marking' => ['validated' => 1],
        ]);
        $nextConference->addParticipation($nextParticipation);

        $liveConference = $this->addConference([
            'name' => 'Live Conf',
            'siteUrl' => 'https://live-conf.test',
            'cfpUrl' => 'https://cfp.live-conf.test',
            'startAt' => new \DateTime('- 1 days'),
            'endAt' => new \DateTime('+ 1 days'),
            'cfpEndAt' => new \DateTime('- 30 days'),
        ]);

        $liveParticipation = $this->addParticipation([
            'participant' => $user2,
            'conference' => $liveConference,
            'asSpeaker' => false,
            'marking' => ['validated' => 1],
        ]);
        $liveConference->addParticipation($liveParticipation);

        $passedConference = $this->addConference([
            'name' => 'Passed Conf',
            'siteUrl' => 'https://passed-conf.test',
            'cfpUrl' => 'https://cfp.passed-conf.test',
            'startAt' => new \DateTime('- 20 days'),
            'endAt' => new \DateTime('- 18 days'),
            'cfpEndAt' => new \DateTime('- 60 days'),
        ]);

        $passedParticipation = $this->addParticipation([
            'participant' => $admin,
            'conference' => $passedConference,
            'asSpeaker' => true,
            'marking' => ['validated' => 1],
        ]);
        $passedConference->addParticipation($passedParticipation);

        $talk1 = $this->addTalk([
            'title' => 'Talk1',
            'intro' => 'Intro1',
        ]);

        $talk2 = $this->addTalk([
            'title' => 'Talk2',
            'intro' => 'Intro2',
        ]);

        $talk3 = $this->addTalk([
            'title' => 'Talk3',
            'intro' => 'Intro3',
        ]);

        $submit1 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1, $admin],
            'talk' => $talk1,
        ]);

        $submit2 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user2, $admin],
            'talk' => $talk1,
        ]);

        $submit3 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1, $admin, $user2],
            'talk' => $talk1,
        ]);

        $submit4 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1],
            'talk' => $talk1,
        ]);

        $submit5 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1],
            'talk' => $talk2,
        ]);

        $submit6 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1],
            'talk' => $talk2,
        ]);

        $submit7 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1, $user2],
            'talk' => $talk2,
        ]);

        $submit8 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$admin],
            'talk' => $talk2,
        ]);

        $submit9 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$user1, $admin],
            'talk' => $talk3,
        ]);

        $submit10 = $this->addSubmit([
            'status' => 'pending',
            'users' => [$admin, $user1],
            'talk' => $talk3,
        ]);

        $manager->flush();
    }
}
