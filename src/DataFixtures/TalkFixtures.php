<?php

namespace App\DataFixtures;

use App\Factory\TalkFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TalkFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        TalkFactory::createMany(100);
    }
}
