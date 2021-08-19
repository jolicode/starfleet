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

use App\Factory\ConferenceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ConferenceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ConferenceFactory::createMany(50);
        ConferenceFactory::createMany(10, [
            'online' => true,
            'country' => null,
            'city' => null,
            'coordinates' => [],
        ]);
        ConferenceFactory::createMany(10, [
            'excluded' => true,
        ]);
        ConferenceFactory::createMany(5, [
            'featured' => true,
        ]);

        $date = new \DateTime('+10 days');
        $date->setTime(0, 0);
        ConferenceFactory::createOne([
            'featured' => true,
            'description' => '',
            'startAt' => $date,
            'endAt' => new \DateTime('+12 days'),
        ]);
    }
}
