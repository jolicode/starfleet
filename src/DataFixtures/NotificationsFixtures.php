<?php

namespace App\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\SubmitFixtures;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\ConferenceFixtures;
use App\DataFixtures\ParticipationFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Factory\Notifications\SubmitAddedNotificationFactory;
use App\Factory\Notifications\SubmitStatusChangedNotificationFactory;
use App\Factory\Notifications\NewFeaturedConferenceNotificationFactory;
use App\Factory\Notifications\ParticipationStatusChangedNotificationFactory;
use App\Entity\Notifications\Notification;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Proxy;

class NotificationsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (UserFactory::all() as $user) {
            foreach (\range(1, \random_int(1, 3)) as $i) {
                $user->addNotification($this->createRandomNotification($user));
            }
        }
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ParticipationFixtures::class,
            ConferenceFixtures::class,
            SubmitFixtures::class,
        ];
    }

    private function createRandomNotification(Proxy $user): Notification
    {
        $proxy = match (random_int(1, 4)) {
            1 => NewFeaturedConferenceNotificationFactory::findOrCreate(['targetUser' => $user]),
            2 => ParticipationStatusChangedNotificationFactory::findOrCreate(['targetUser' => $user]),
            3 => SubmitAddedNotificationFactory::findOrCreate(['targetUser' => $user]),
            4 => SubmitStatusChangedNotificationFactory::findOrCreate(['targetUser' => $user]),
        };

        return $proxy->object();
    }
}
