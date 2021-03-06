<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Entity;

use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use Generator;
use PHPUnit\Framework\TestCase;

class TalkTest extends TestCase
{
    /**
     * @dataProvider provideUsers
     */
    public function testGetSubmitsByUser(array $submits, int $expectedAmount)
    {
        $talk = new Talk();

        foreach ($submits as $submit) {
            $newSubmit = new Submit();

            foreach ($submit as $user) {
                $newUser = new User();
                $newUser->setName($user);

                $newSubmit->addUser($newUser);
            }

            $talk->addSubmit($newSubmit);
        }

        $result = $talk->getUniqueUsersNames();
        self::assertCount($expectedAmount, $result);
    }

    public function provideUsers(): Generator
    {
        yield [
            'submits' => [['user1'], ['user1']],
            'expectedAmount' => 1,
        ];
        yield [
            'submits' => [['user1'], ['user2']],
            'expectedAmount' => 2,
        ];
        yield [
            'submits' => [['user1', 'user2'], ['user1', 'user2']],
            'expectedAmount' => 1,
        ];
        yield [
            'submits' => [['user2', 'user1'], ['user1', 'user2']],
            'expectedAmount' => 1,
        ];
        yield [
            'submits' => [['user2', 'user1'], ['user1']],
            'expectedAmount' => 2,
        ];
        yield [
            'submits' => [['user1', 'user2'], ['user1'], ['user1'], ['user2'], ['user1', 'user2', 'user3']],
            'expectedAmount' => 4,
        ];
    }
}
