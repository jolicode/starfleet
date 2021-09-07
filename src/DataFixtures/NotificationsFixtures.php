<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Notifications\AbstractNotification;
use App\Factory\Notifications\NewFeaturedConferenceNotificationFactory;
use App\Factory\Notifications\NewSubmitNotificationFactory;
use App\Factory\Notifications\ParticipationStatusChangedNotificationFactory;
use App\Factory\Notifications\SubmitStatusChangedNotificationFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Proxy;

class NotificationsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (UserFactory::all() as $user) {
            foreach (range(1, random_int(1, 3)) as $i) {
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

    private function createRandomNotification(Proxy $user): AbstractNotification
    {
        $proxy = match (random_int(1, 4)) {
            1 => NewFeaturedConferenceNotificationFactory::findOrCreate(['targetUser' => $user]),
            2 => ParticipationStatusChangedNotificationFactory::findOrCreate(['targetUser' => $user]),
            3 => NewSubmitNotificationFactory::findOrCreate(['targetUser' => $user]),
            4 => SubmitStatusChangedNotificationFactory::findOrCreate(['targetUser' => $user]),
        };

        return $proxy->object();
    }
}
