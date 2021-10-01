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
use App\Factory\Notifications\NewSubmitNotificationFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group notifications
 */
class NewSubmitNotificationTest extends AbstractStarfleetTest
{
    use Factories;

    public function testNotificationIsCreated()
    {
        $emitterUser = UserFactory::find(['name' => 'Emitter']);
        $targetUser = UserFactory::find(['name' => 'Target']);
        $conference = ConferenceFactory::find(['name' => 'Future Conference']);
        $talk = TalkFactory::random();

        $client = $this->getClient($emitterUser->object());
        $client->request('GET', '/user/submits');
        $crawler = $client->submitForm('submit_submit', [
            'submit[conference]' => $conference->getName(),
            'submit[talk]' => $talk->getId(),
            'submit[users]' => [$emitterUser->getId(), $targetUser->getId()],
        ]);

        $targetUser->refresh();

        $allNotifications = NewSubmitNotificationFactory::all();
        self::assertCount(1, $allNotifications);
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
        UserFactory::createOne([
            'name' => 'Emitter',
        ]);
        UserFactory::createOne([
            'name' => 'Target',
        ]);
        $conference = ConferenceFactory::createOne([
            'name' => 'Future Conference',
            'startAt' => new \DateTime('+10 days'),
            'endAt' => new \DateTime('+12 days'),
        ]);
        $talk = TalkFactory::createOne();
        $user = UserFactory::createOne();
        SubmitFactory::createOne([
            'users' => [$user],
            'talk' => $talk,
            'conference' => $conference,
        ]);
    }
}
