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

use App\Entity\Continent;
use Doctrine\Persistence\ObjectManager;

class ContinentFixtures extends AbstractFixtures
{
    public function load(ObjectManager $manager): void
    {
        $continents = [
            'Africa',
            'Asia',
            'Europe',
            'North America',
            'Oceania',
            'South America',
        ];

        foreach ($continents as $continentName) {
            $continent = new Continent();
            $continent->setName($continentName);
            $continent->setEnabled(true);

            $manager->persist($continent);
        }

        $manager->flush();
    }
}
