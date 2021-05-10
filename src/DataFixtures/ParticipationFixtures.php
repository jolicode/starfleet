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

use App\Enum\Workflow\Transition\Participation;
use App\Factory\ParticipationFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ParticipationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        ParticipationFactory::createMany(50);
        ParticipationFactory::createMany(50, [
            'marking' => Participation::ACCEPTED,
        ]);
        ParticipationFactory::createMany(50, [
            'marking' => Participation::REJECTED,
        ]);
        ParticipationFactory::createMany(50, [
            'marking' => Participation::CANCELLED,
        ]);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ConferenceFixtures::class,
        ];
    }
}
