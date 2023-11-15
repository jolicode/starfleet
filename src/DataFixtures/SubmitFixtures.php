<?php

namespace App\DataFixtures;

use App\Entity\Submit;
use App\Factory\SubmitFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SubmitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        SubmitFactory::createMany(3, [
            'users' => UserFactory::findBy(['name' => 'User']),
            'status' => Submit::STATUS_ACCEPTED,
        ]);
        SubmitFactory::createMany(3, [
            'users' => UserFactory::findBy(['name' => 'User']),
            'status' => Submit::STATUS_DONE,
        ]);
        SubmitFactory::createMany(3, [
            'users' => UserFactory::findBy(['name' => 'User']),
            'status' => Submit::STATUS_PENDING,
        ]);
        SubmitFactory::createMany(3, [
            'users' => UserFactory::findBy(['name' => 'User']),
            'status' => Submit::STATUS_REJECTED,
        ]);
        SubmitFactory::createMany(300);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TalkFixtures::class,
            ConferenceFixtures::class,
        ];
    }
}
