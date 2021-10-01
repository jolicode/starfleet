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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group notifications
 */
class SubmitCancelledNotificationTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testNotificationIsCreated()
    {
        $emitterUser = UserFactory::createOne();
        $targetUser = UserFactory::createOne();
        $submit = SubmitFactory::createOne([
            'conference' => ConferenceFactory::createOne(),
            'talk' => TalkFactory::createOne(),
            'users' => [$emitterUser, $targetUser],
            'status' => Submit::STATUS_PENDING,
        ]);

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($emitterUser->object());
        $client->request('GET', sprintf('/user/submits', $submit->getId()));

        $client->submitForm('Cancel');
        $targetUser->refresh();

        self::assertCount(1, SubmitCancelledNotificationFactory::all());
        self::assertCount(0, SubmitFactory::all());
        self::assertSame($targetUser->object(), SubmitCancelledNotificationFactory::find(1)->getTargetUser());

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($targetUser->object());
        $crawler = $client->request('GET', '/user/account');

        $notificationsButton = $crawler->filter('div.notification-button');
        self::assertSame('1 Notifications', $notificationsButton->text());
    }
}
