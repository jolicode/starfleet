<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Controller\User;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testUserPageForUsers()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneBy(['name' => 'User']);

        $client->loginUser($user);
        $crawler = $client->request('GET', '/user/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $user->getName());
        self::assertCount(0, $crawler->filter('#admin-button'));
    }

    public function testUserPageForAdmins()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneBy(['name' => 'Admin']);

        $client->loginUser($user);
        $crawler = $client->request('GET', '/user/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $user->getName());
        self::assertCount(1, $crawler->filter('#admin-button'));
    }
}
