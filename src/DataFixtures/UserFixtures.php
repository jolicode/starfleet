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

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@starfleet.app');
        $admin->setName('Admin');
        $admin->addRole('ROLE_ADMIN');
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'password'));
        $manager->persist($admin);
        $this->setReference('admin', $admin);

        $user = new User();
        $user->setEmail('user@starfleet.app');
        $user->setName('User');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
        $manager->persist($user);
        $this->setReference('user', $user);

        $manager->flush();
    }
}
