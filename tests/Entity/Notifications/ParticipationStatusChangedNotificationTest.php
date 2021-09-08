<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Entity\Notifications;

use App\Factory\ConferenceFactory;
use App\Factory\Notifications\ParticipationStatusChangedNotificationFactory;
use App\Factory\ParticipationFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group notifications
 */
class ParticipationStatusChangedNotificationTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testNotificationIsCreated()
    {
        $testAdmin = UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]);
        $testUser = UserFactory::createOne([
            'roles' => ['ROLE_USER'],
        ]);
        ParticipationFactory::createOne([
            'participant' => $testUser,
            'conference' => ConferenceFactory::createOne(),
            'marking' => 'pending',
        ]);

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($testAdmin->object());
        $client->request('GET', '/admin/?entity=Participation&action=list&menuIndex=3&submenuIndex=-1');

        $crawler = $client->clickLink('Accept');

        self::assertCount(1, ParticipationStatusChangedNotificationFactory::all());
        self::assertSame($testUser->object(), ParticipationStatusChangedNotificationFactory::find(1)->getTargetUser());

        $client->loginUser($testUser->object());
        $crawler = $client->request('GET', '/user/account');

        $notificationsButton = $crawler->filter('div.notification-button');
        self::assertSame('1 Notifications', $notificationsButton->text());
    }
}
