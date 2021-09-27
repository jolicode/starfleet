<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Repository;

use App\Entity\Submit;
use App\Factory\ConferenceFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Repository\SubmitRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SubmitRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testUpdateDoneSubmitsWork()
    {
        UserFactory::createMany(2);
        ConferenceFactory::createMany(2, [
            'startAt' => new \DateTime('-1 years'),
            'endAt' => new \DateTime('-1 years'),
        ]);
        TalkFactory::createMany(2);
        SubmitFactory::createOne([
            'conference' => ConferenceFactory::find(1),
            'talk' => TalkFactory::find(1),
            'status' => Submit::STATUS_ACCEPTED,
        ]);
        SubmitFactory::createOne([
            'conference' => ConferenceFactory::find(2),
            'talk' => TalkFactory::find(2),
            'status' => Submit::STATUS_ACCEPTED,
        ]);

        static::$container->get(SubmitRepository::class)->updateDoneSubmits();

        self::assertCount(0, SubmitFactory::findBy(['status' => Submit::STATUS_ACCEPTED]));
        self::assertCount(2, SubmitFactory::findBy(['status' => Submit::STATUS_DONE]));
    }
}
