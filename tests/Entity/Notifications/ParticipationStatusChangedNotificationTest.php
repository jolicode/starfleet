<?php

namespace App\Tests\Entity\Notifications;

use App\Factory\ConferenceFactory;
use App\Factory\Notifications\ParticipationStatusChangedNotificationFactory;
use App\Factory\ParticipationFactory;
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group notifications
 */
class ParticipationStatusChangedNotificationTest extends AbstractStarfleetTest
{
    use Factories;

    public function testNotificationIsCreated()
    {
        $client = $this->getClient($this->getAdminUser());
        $client->request('GET', '/admin/?entity=Participation&action=list&menuIndex=3&submenuIndex=-1');

        $crawler = $client->clickLink('Accept');
        $allNotifications = ParticipationStatusChangedNotificationFactory::all();

        self::assertCount(1, $allNotifications);
        self::assertSame($this->getTestUser(), $allNotifications[0]->getTargetUser());

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($this->getTestUser());
        $crawler = $client->request('GET', '/user/account');

        $notificationsButton = $crawler->filter('div.notification-button');
        self::assertSame('1 Notifications', $notificationsButton->text());
    }

    protected function generateData()
    {
        $testUser = $this->getTestUser();
        $this->getAdminUser();
        ParticipationFactory::createOne([
            'participant' => $testUser,
            'conference' => ConferenceFactory::createOne(),
            'marking' => 'pending',
        ]);
    }
}
