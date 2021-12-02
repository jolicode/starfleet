<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Fetcher;

use App\Entity\Continent;
use App\Fetcher\ConfTechCloner;
use App\Fetcher\ConfTechFetcher;
use App\Fetcher\LocationGuesser;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ConfTechFetcherTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideConferences
     */
    public function testFetch(array $rawConference, array $expectedItems, array $tags)
    {
        $result = $this
            ->createFetcher()
            ->fetch([
                'tags' => $tags,
                'now' => new \DateTime('2021-01-01'),
            ])
        ;

        if (!$expectedItems['expectedCity']) {
            return self::assertEmpty(iterator_to_array($result));
        }

        foreach ($result as $fetchedConference) {
            self::assertSame($rawConference['name'], $fetchedConference->getName());
            self::assertSame($rawConference['url'], $fetchedConference->getSiteUrl());
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['startDate'])->format('Y-m-d') === $fetchedConference->getStartAt()->format('Y-m-d'));
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['endDate'])->format('Y-m-d') === $fetchedConference->getEndAt()->format('Y-m-d'));
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['cfpEndDate'])->format('Y-m-d') === $fetchedConference->getCfpEndAt()->format('Y-m-d'));
            self::assertSame($rawConference['cfpUrl'], $fetchedConference->getCfpUrl());
            self::assertSame($expectedItems['expectedCity'], $fetchedConference->getCity());
            self::assertSame($expectedItems['online'], $fetchedConference->isOnline());
        }
    }

    public function provideConferences(): \Generator
    {
        yield 'Test normal PHP conference is correctly denormalized' => [
            'rawConference' => [
                'name' => 'ConFoo',
                'url' => 'https://confoo.ca/en/yul2021',
                'startDate' => '3021-02-24',
                'endDate' => '3021-02-26',
                'city' => 'Montreal',
                'country' => 'Canada',
                'cfpUrl' => 'https://confoo.ca/en/yul2021/call-for-papers',
                'cfpEndDate' => '2020-10-16',
                'cocUrl' => 'https://confoo.ca/en/code-of-conduct',
            ],
            'expectedItems' => [
                'expectedCity' => 'Montreal',
                'online' => false,
            ],
            'tags' => ['php'],
        ];

        yield 'Test online CSS conference is correctly denormalized' => [
            'rawConference' => [
                'name' => 'cssday Digital Edition',
                'url' => 'https://2021.cssday.it',
                'startDate' => '3021-03-11',
                'endDate' => '3021-03-11',
                'online' => true,
                'cfpUrl' => 'https://2021.cssday.it/welcome/cfp.html',
                'cfpEndDate' => '2021-01-31',
                'twitter' => '@cssday_it',
                'cocUrl' => 'https://2021.cssday.it/welcome/coc.html',
                'offersSignLanguageOrCC' => false,
            ],
            'expectedItems' => [
                'expectedCity' => 'Online',
                'online' => true,
            ],
            'tags' => ['css'],
        ];

        yield 'Test not configured fetcher doesn\'t fetch anything' => [
            'rawConference' => [],
            'expectedItems' => [
                'expectedCity' => null,
            ],
            'tags' => [],
        ];

        yield 'Test past conference is not fetched' => [
            'rawConference' => [
                'name' => 'HalfStack Phoenix',
                'url' => 'https://halfstackconf.com/phoenix',
                'startDate' => '1000-01-15',
                'endDate' => '1000-01-15',
                'city' => 'Phoenix, AZ',
                'country' => 'U.S.A.',
                'cfpUrl' => 'https://halfstackconf.com/phoenix/',
                'cfpEndDate' => '2020-08-31',
                'twitter' => '@halfstackconf',
                'cocUrl' => 'https://halfstackconf.com/phoenix/code-of-conduct/',
            ],
            'expectedItems' => [
                'expectedCity' => null,
            ],
            'tags' => ['javascript'],
        ];
    }

    private function createFetcher(): ConfTechFetcher
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

        $locationGuesser
            ->getCountry(Argument::type('string'))
            ->willReturn('FR')
        ;

        $confTechCloner = $this->prophesize(ConfTechCloner::class);
        $confTechCloner
            ->clone()
            ->willReturn(\dirname(__DIR__) . '/Fixtures/conftech_data/conferences')
        ;

        return new ConfTechFetcher(
            $locationGuesser->reveal(),
            new Filesystem(),
            $confTechCloner->reveal(),
        );
    }
}
