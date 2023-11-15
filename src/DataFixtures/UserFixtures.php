<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'name' => 'Admin',
            'email' => 'admin@starfleet.app',
            'password' => 'admin',
            'roles' => ['ROLE_ADMIN'],
        ]);
        UserFactory::createOne([
            'name' => 'User',
            'email' => 'user@starfleet.app',
            'password' => 'user',
        ]);
        UserFactory::createMany(10);
    }
}
