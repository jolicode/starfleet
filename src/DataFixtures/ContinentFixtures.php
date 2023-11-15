<?php

namespace App\DataFixtures;

use App\Factory\ContinentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContinentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ContinentFactory::createMany(6);
    }
}
