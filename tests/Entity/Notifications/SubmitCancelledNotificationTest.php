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

use App\Entity\Submit;
use App\Factory\ConferenceFactory;
use App\Factory\Notifications\SubmitCancelledNotificationFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group notifications
 */
class SubmitCancelledNotificationTest extends AbstractStarfleetTest
{
    use Factories;

    public function testNotificationIsCreated()
    {
        $emitterUser = UserFactory::find(['name' => 'Emitter']);
        $targetUser = UserFactory::find(['name' => 'Target']);

        $client = $this->getClient($emitterUser->object());
        $client->request('GET', '/user/submits');

        $client->submitForm('Cancel');
        $targetUser->refresh();

        $allNotifications = SubmitCancelledNotificationFactory::all();
        self::assertCount(1, $allNotifications);
        self::assertCount(0, SubmitFactory::all());
        self::assertSame($targetUser->object(), $allNotifications[0]->getTargetUser());

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($targetUser->object());
        $crawler = $client->request('GET', '/user/account');

        $notificationsButton = $crawler->filter('div.notification-button');
        self::assertSame('1 Notifications', $notificationsButton->text());
    }

    protected function generateData()
    {
        $emitterUser = UserFactory::createOne([
            'name' => 'Emitter',
        ]);
        $targetUser = UserFactory::createOne([
            'name' => 'Target',
        ]);
        SubmitFactory::createOne([
            'conference' => ConferenceFactory::createOne(),
            'talk' => TalkFactory::createOne(),
            'users' => [$emitterUser, $targetUser],
            'status' => Submit::STATUS_PENDING,
        ]);
    }
}
