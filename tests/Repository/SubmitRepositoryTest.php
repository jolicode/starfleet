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
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

class SubmitRepositoryTest extends AbstractStarfleetTest
{
    use Factories;

    public function testUpdateDoneSubmitsWork()
    {
        $this->getContainer()->get(SubmitRepository::class)->updateDoneSubmits();

        self::assertCount(0, SubmitFactory::findBy(['status' => Submit::STATUS_ACCEPTED]));
        self::assertCount(2, SubmitFactory::findBy(['status' => Submit::STATUS_DONE]));
    }

    protected function generateData()
    {
        UserFactory::createMany(2);
        ConferenceFactory::createOne([
            'startAt' => new \DateTime('-1 years'),
            'endAt' => new \DateTime('-1 years'),
        ]);
        TalkFactory::createOne();
        SubmitFactory::createOne([
            'conference' => ConferenceFactory::random(),
            'talk' => TalkFactory::random(),
            'status' => Submit::STATUS_ACCEPTED,
        ]);
        SubmitFactory::createOne([
            'conference' => ConferenceFactory::random(),
            'talk' => TalkFactory::random(),
            'status' => Submit::STATUS_ACCEPTED,
        ]);
    }
}
