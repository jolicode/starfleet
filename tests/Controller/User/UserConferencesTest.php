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

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserConferencesTest extends WebTestCase
{
    private ?User $user = null;

    public function testPageWorks()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/future-conferences');

        self::assertResponseIsSuccessful();
    }

    public function testAskParticipationWork()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/future-conferences');

        $form = $crawler
            ->filter('form.action-ask-participation')
            ->first()
            ->form()
        ;

        $client->click($form);

        self::assertResponseIsSuccessful();
    }

    public function testSubmitTalkWork()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/future-conferences');

        $form = $crawler
            ->filter('form.action-submit')
            ->first()
            ->form()
        ;

        $client->click($form);

        self::assertResponseIsSuccessful();
    }

    private function getUser(): User
    {
        if (null === $this->user) {
            $userRepository = static::$container->get(UserRepository::class);
            $this->user = $userRepository->findOneBy(['name' => 'User']);
        }

        return $this->user;
    }
}
