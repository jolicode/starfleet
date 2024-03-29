<?php

namespace App\Tests\Controller\UserAccount;

use App\Tests\AbstractStarfleetTest;

/**
 * @group user_account
 */
class HomepageControllerTest extends AbstractStarfleetTest
{
    public function testUserPageForUsers()
    {
        $crawler = $this->getClient()->request('GET', '/user/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.username', $this->getTestUser()->getName());
        self::assertCount(0, $crawler->filter('#admin-button'));
    }

    public function testUserPageForAdmins()
    {
        $user = $this->getAdminUser();
        $crawler = $this->getClient($user)->request('GET', '/user/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.username', $user->getName());
        self::assertCount(1, $crawler->filter('#admin-button'));
    }

    protected function generateData()
    {
        // No specific data needed for these tests
    }
}
