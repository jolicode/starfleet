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

use App\Entity\Conference;
use App\Entity\Participation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ConferenceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $nextConference = new Conference();
        $nextConference->setName('Next Conf');
        $nextConference->setSiteUrl('https://next-conf.test');
        $nextConference->setLocation('/dev/null');
        $nextConference->setStartAt(new \DateTime('+ 10 days'));
        $nextConference->setEndAt(new \DateTime('+ 11 days'));
        $nextConference->setCfpUrl('https://cfp.next-conf.test');
        $nextConference->setCfpEndAt(new \DateTime('+ 3 days'));

        $nextParticipation = new Participation();
        $nextParticipation->setParticipant($this->getReference('user'));
        $nextParticipation->setMarking(['validated' => 1]);
        $nextParticipation->setAsSpeaker(true);
        $nextConference->addParticipation($nextParticipation);

        $liveConference = new Conference();
        $liveConference->setName('Live Conf');
        $liveConference->setSiteUrl('https://live-conf.test');
        $liveConference->setLocation('/dev/null');
        $liveConference->setStartAt(new \DateTime('-1 days'));
        $liveConference->setEndAt(new \DateTime('+ 1 days'));
        $liveConference->setCfpUrl('https://cfp.live-conf.test');
        $liveConference->setCfpEndAt(new \DateTime('- 30 days'));

        $liveParticipation = new Participation();
        $liveParticipation->setParticipant($this->getReference('user'));
        $liveParticipation->setMarking(['validated' => 1]);
        $liveParticipation->setAsSpeaker(false);
        $liveConference->addParticipation($liveParticipation);

        $passedConference = new Conference();
        $passedConference->setName('Passed Conf');
        $passedConference->setSiteUrl('https://passed-conf.test');
        $passedConference->setLocation('/dev/null');
        $passedConference->setStartAt(new \DateTime('- 20 days'));
        $passedConference->setEndAt(new \DateTime('- 18 days'));
        $passedConference->setCfpUrl('https://cfp.passed-conf.test');
        $passedConference->setCfpEndAt(new \DateTime('- 60 days'));

        $passedParticipation = new Participation();
        $passedParticipation->setParticipant($this->getReference('user'));
        $passedParticipation->setMarking(['validated' => 1]);
        $passedParticipation->setAsSpeaker(false);
        $passedConference->addParticipation($passedParticipation);

        $manager->persist($nextConference);
        $manager->persist($liveConference);
        $manager->persist($passedConference);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
