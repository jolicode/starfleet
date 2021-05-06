<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Conference;

use App\Conferences\ConferencesHarvester;
use App\Entity\Conference;
use App\Entity\ConferenceFilter;
use App\Entity\FetcherConfiguration;
use App\Event\DailyNotificationEvent;
use App\Fetcher\TululaFetcher;
use App\Repository\ConferenceFilterRepository;
use App\Repository\ConferenceRepository;
use App\Repository\FetcherConfigurationRepository;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConferenceHarvesterTest extends KernelTestCase
{
    /**
     * @dataProvider provideFetcherResponses
     */
    public function testFetch(int $expectedUpdatedConferences, int $expectedAddedConferences, string $name, bool $isActive, Conference $conference, ?Conference $existingConference)
    {
        $results = $this->createHarvester($name, $isActive, $conference, $existingConference)->harvest();

        self::assertSame($expectedUpdatedConferences, $results['updatedConferencesCount']);
        self::assertSame($expectedAddedConferences, $results['newConferencesCount']);
    }

    public function provideFetcherResponses()
    {
        $testConference = new Conference();
        $testConference
            ->setName('TestConference')
            ->setStartAt(new \DateTime());

        $differentTestConference = new Conference();
        $differentTestConference
            ->setName('OtherTestConference')
            ->setStartAt(new \DateTime('+1 year'));

        $ignoredTestConference = new Conference();
        $ignoredTestConference
            ->setName('Java Conference');

        yield 'test fetchers add new conference' => [
            'expectedUpdatedConferences' => 0,
            'expectedAddedConferences' => 1,
            'name' => TululaFetcher::class,
            'isActive' => true,
            'conference' => $testConference,
            'existingConference' => null,
        ];

        yield 'test fetchers update existing conference' => [
            'expectedUpdatedConferences' => 1,
            'expectedAddedConferences' => 0,
            'name' => TululaFetcher::class,
            'isActive' => true,
            'conference' => $testConference,
            'existingConference' => $differentTestConference,
        ];

        yield 'test inactive fetcher' => [
            'expectedUpdatedConferences' => 0,
            'expectedAddedConferences' => 0,
            'name' => TululaFetcher::class,
            'isActive' => false,
            'conference' => $testConference,
            'existingConference' => null,
        ];

        yield 'conference should be ignored' => [
            'expectedUpdatedConferences' => 0,
            'expectedAddedConferences' => 0,
            'name' => TululaFetcher::class,
            'isActive' => true,
            'conference' => $ignoredTestConference,
            'existingConference' => null,
        ];
    }

    private function createHarvester(string $name, bool $isActive, Conference $conference, ?Conference $existingConference)
    {
        $fetcherProphecy = $this->prophesize($name);
        $fetcherProphecy
            ->fetch([])
            ->willYield([$conference]);

        $fetcherConfigurationRepository = $this->prophesize(FetcherConfigurationRepository::class);
        $fetcherConfigurationRepository
            ->findOneOrCreate(Argument::type('string'))
            ->willReturn($fetcherConfiguration = new FetcherConfiguration('Test'));
        $fetcherConfiguration->setActive($isActive);

        $conferenceFilterRepository = $this->prophesize(ConferenceFilterRepository::class);
        $conferenceFilterRepository
            ->findAll()
            ->willReturn([$filter = new ConferenceFilter()]);
        $filter->setName('java');

        $conferenceRepository = $this->prophesize(ConferenceRepository::class);
        $conferenceRepository
            ->findExistingConference(Argument::type(Conference::class))
            ->willReturn($existingConference);
        $conferenceRepository
            ->getEndingCfpsByRemainingDays()
            ->willReturn([]);

        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager
            ->persist(Argument::type(Conference::class));
        $entityManager
            ->flush();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher
            ->dispatch(Argument::type(DailyNotificationEvent::class));

        $harvester = new ConferencesHarvester(
            new \ArrayIterator([$fetcherProphecy->reveal()]),
            $fetcherConfigurationRepository->reveal(),
            $conferenceFilterRepository->reveal(),
            $conferenceRepository->reveal(),
            $entityManager->reveal(),
            $eventDispatcher->reveal()
        );

        return $harvester;
    }
}
