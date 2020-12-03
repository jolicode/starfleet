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

        $talk = new Talk();
        $talk->setTitle('TalkTitle2');
        $talk->setIntro('TalkIntro2');
        $manager->persist($talk);
        $this->setReference('talk2', $talk);

        $talk = new Talk();
        $talk->setTitle('TalkTitle3');
        $talk->setIntro('TalkIntro3');
        $manager->persist($talk);
        $this->setReference('talk3', $talk);

        $manager->flush();
    }
}
