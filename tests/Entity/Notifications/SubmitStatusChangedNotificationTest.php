<?php

namespace App\Tests\Entity\Notifications;

use App\Entity\Submit;
use App\Factory\ConferenceFactory;
use App\Factory\Notifications\SubmitStatusChangedNotificationFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group notifications
 */
class SubmitStatusChangedNotificationTest extends AbstractStarfleetTest
{
    use Factories;

    public function testNotificationIsCreated()
    {
        $emitterUser = UserFactory::find(['name' => 'Emitter']);
        $targetUser = UserFactory::find(['name' => 'Target']);
        $submit = SubmitFactory::random();

        $client = $this->getClient($emitterUser->object());
        $client->request('GET', '/user/submits');

        $client->submitForm('Accept');
        $submit->refresh()->save();
        $targetUser->refresh();

        $allNotifications = SubmitStatusChangedNotificationFactory::all();
        self::assertCount(1, $allNotifications);
        self::assertSame(Submit::STATUS_ACCEPTED, $allNotifications[0]->getSubmit()->getStatus());

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
