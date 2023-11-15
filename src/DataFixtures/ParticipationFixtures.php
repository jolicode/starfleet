<?php

namespace App\DataFixtures;

use App\Enum\Workflow\Transition\Participation;
use App\Factory\ConferenceFactory;
use App\Factory\ParticipationFactory;
use App\Factory\UserFactory;
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
        ParticipationFactory::createMany(3, [
            'participant' => UserFactory::find(['name' => 'User']),
            'marking' => Participation::ACCEPTED,
            'conference' => ConferenceFactory::findOrCreate([
                'startAt' => new \DateTime('+13 days'),
                'endAt' => new \DateTime('+15 days'),
            ]),
        ]);
        ParticipationFactory::createMany(3, [
            'participant' => UserFactory::find(['name' => 'User']),
            'marking' => Participation::ACCEPTED,
            'conference' => ConferenceFactory::findOrCreate([
                'startAt' => new \DateTime('-15 days'),
                'endAt' => new \DateTime('-13 days'),
            ]),
        ]);
        ParticipationFactory::createMany(3, [
            'participant' => UserFactory::find(['name' => 'User']),
            'marking' => Participation::REJECTED,
        ]);
        ParticipationFactory::createMany(3, [
            'participant' => UserFactory::find(['name' => 'User']),
            'marking' => Participation::PENDING,
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
