<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Fetcher;

use App\Entity\Continent;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SymfonyFetcherTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideConferences
     */
    public function testFetch(array $rawConference, array $expectedItems, array $fetcherConfig = [])
    {
        $response = new MockResponse(json_encode([$rawConference]));
        $result = $this->createFetcher($response)->fetch($fetcherConfig);

        if (!$expectedItems['expectedCity']) {
            return self::assertEmpty(iterator_to_array($result));
        }

        foreach ($result as $fetchedConference) {
            self::assertSame($rawConference['name'], $fetchedConference->getName());
            self::assertSame($rawConference['home_url'], $fetchedConference->getSiteUrl());
            self::assertTrue((new \DateTimeImmutable($rawConference['starts_at']['date']))->format('Y-m-d') === $fetchedConference->getStartAt()->format('Y-m-d'));
            self::assertTrue((new \DateTimeImmutable($rawConference['ends_at']['date']))->format('Y-m-d') === $fetchedConference->getEndAt()->format('Y-m-d'));
            self::assertTrue((new \DateTimeImmutable($rawConference['cfp_ends_at']['date']))->format('Y-m-d') === $fetchedConference->getCfpEndAt()->format('Y-m-d'));
            self::assertSame($rawConference['registration_url'], $fetchedConference->getCfpUrl());
            self::assertSame($rawConference['slug'], $fetchedConference->getSlug());
            self::assertSame($expectedItems['expectedCity'], $fetchedConference->getCity());
            self::assertSame($rawConference['is_online'], $fetchedConference->isOnline());
            self::assertSame(['Symfony', 'PHP'], $fetchedConference->getTags());
        }
    }

    public function provideConferences(): \Generator
    {
        yield 'Test normal Conference is correctly denormalized' => [
            'rawConference' => [
                'name' => 'Symfony "We Love Rock" World Tour',
                'home_url' => 'https://symfony-hard-rock-world-tour',
                'starts_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'ends_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'cfp_starts_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'cfp_ends_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'registration_url' => 'https://come-rock-with-symfony',
                'slug' => 'for-those-about-to-symfony',
                'city' => 'Sydney',
                'country' => 'AU',
                'is_online' => false,
            ],
            'expectedItems' => [
                'expectedCity' => 'Sydney',
            ],
        ];

        yield 'Test online Conference is correctly denormalized' => [
            'rawConference' => [
                'name' => 'Symfony shakes your basement',
                'home_url' => 'https://symfony-house-rock',
                'starts_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'ends_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'cfp_starts_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'cfp_ends_at' => ['date' => '3022-04-17 00:00:00.000000'],
                'registration_url' => 'https://rock-online-with-symfony',
                'slug' => 'for-those-about-to-symfony',
                'city' => 'Sydney',
                'country' => 'AU',
                'is_online' => true,
            ],
            'expectedItems' => [
                'expectedCity' => 'Online',
            ],
        ];

        yield 'Test past Conference is not fetched' => [
            'rawConference' => [
                'name' => 'Symfony 1 is incredible',
                'home_url' => 'https://symfony-first-conference',
                'starts_at' => ['date' => '1022-04-17 00:00:00.000000'],
                'ends_at' => ['date' => '1022-04-17 00:00:00.000000'],
                'cfp_starts_at' => ['date' => '1022-04-17 00:00:00.000000'],
                'cfp_ends_at' => ['date' => '1022-04-17 00:00:00.000000'],
                'registration_url' => 'https://discover-our-new-framework',
                'slug' => 'fabpot-superstar',
                'city' => 'Paris',
                'country' => 'FR',
                'is_online' => false,
            ],
            'expectedItems' => [
                'expectedCity' => null,
            ],
        ];
    }

    public function testRealResponse()
    {
        $realSymfonyData = file_get_contents(\dirname(__DIR__) . '/Fixtures/symfony.json');
        $response = new MockResponse($realSymfonyData);
        $result = $this
            ->createFetcher($response)
            ->fetch()
        ;

        self::assertNotEmpty(iterator_to_array($result));
    }

    private function createFetcher(MockResponse $response): SymfonyFetcher
    {
        $locationGuesser = $this->prophesize(LocationGuesser::class);
        $locationGuesser
            ->getContinent(Argument::type('string'))
            ->willReturn($continent = new Continent())
        ;
        $continent->setName('Europe');
        $continent->setEnabled(true);

        $locationGuesser
            ->getCoordinates(Argument::type('string'))
            ->willReturn([666, 666])
        ;

        $client = new MockHttpClient($response);

        return new SymfonyFetcher(
            $locationGuesser->reveal(),
            $client
        );
    }
}
