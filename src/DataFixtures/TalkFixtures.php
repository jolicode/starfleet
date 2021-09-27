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
