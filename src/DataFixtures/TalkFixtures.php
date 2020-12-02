<?php

namespace App\DataFixtures;

use App\Entity\Talk;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TalkFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $talk = new Talk();
        $talk->setTitle('TalkTitle');
        $talk->setIntro('TalkIntro');
        $manager->persist($talk);
        $this->setReference('talk', $talk);

        $manager->flush();
    }
}
