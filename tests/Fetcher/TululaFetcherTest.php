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
use App\Entity\Tag;
use App\Fetcher\LocationGuesser;
use App\Fetcher\TululaFetcher;
use App\Repository\ExcludedTagRepository;
use App\Repository\TagRepository;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TululaFetcherTest extends KernelTestCase
{
    /**
     * @dataProvider provideConferences
     */
    public function testFetch(array $rawConference, array $expectedItems)
    {
        $data['data']['events']['events'][] = $rawConference;

        $response = new MockResponse(json_encode($data));
        $result = $this->createFetcher($response, $expectedItems)->fetch();

        // If the tag is not selected, the conference should not be yielded and the response should be empty
        if (null === $expectedItems['expectedTag']) {
            return self::assertEmpty(iterator_to_array($result));
        }

        $fetchedConference = iterator_to_array($result)[0][0];

        self::assertSame($rawConference['name'], $fetchedConference->getName());
        self::assertSame($rawConference['url'], $fetchedConference->getSiteUrl());
        self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['dateStart'])->format('Y-m-d') === $fetchedConference->getStartAt()->format('Y-m-d'));
        self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['dateEnd'])->format('Y-m-d') === $fetchedConference->getEndAt()->format('Y-m-d'));
        self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['cfpDateEnd'])->format('Y-m-d') === $fetchedConference->getCfpEndAt()->format('Y-m-d'));
        self::assertSame($rawConference['cfpUrl'], $fetchedConference->getCfpUrl());
        self::assertSame($rawConference['isOnline'], $fetchedConference->isOnline());
        self::assertSame($rawConference['slug'], $fetchedConference->getSlug());
        self::assertSame($expectedItems['expectedCity'], $fetchedConference->getCity());
    }

    public function provideConferences(): \Generator
    {
        yield [
            [
                'name' => 'normal PHP Tour',
                'url' => 'https://php',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/php',
                'isOnline' => false,
                'slug' => 'php-tour-2000',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'Maubeuge',
                ],
                'tags' => [
                    [
                        'name' => 'php',
                    ],
                ],
            ],
            [
                'expectedTag' => 'php',
                'expectedCity' => 'Maubeuge',
            ],
        ];
        yield [
            [
                'name' => 'Symfony World Online',
                'url' => 'https://sfworld',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/sf',
                'isOnline' => true,
                'slug' => 'sf-world-2000',
                'venue' => null,
                'tags' => [
                    [
                        'name' => 'php',
                    ],
                    [
                        'name' => 'pas de bol :(',
                    ],
                ],
            ],
            [
                'expectedTag' => 'php',
                'expectedCity' => 'Online',
            ],
        ];
        yield [
            [
                'name' => 'Tulula provided city in the state field',
                'url' => 'https://weirdo',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/xfiles',
                'isOnline' => false,
                'slug' => 'tulula-went-full-xfiles',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => 'X-Files Land',
                    'city' => '',
                ],
                'tags' => [
                    [
                        'name' => 'php',
                    ],
                ],
            ],
            [
                'expectedTag' => 'php',
                'expectedCity' => 'X-Files Land',
            ],
        ];
        yield [
            [
                'name' => 'Tulula didn\'t provide the city name at all',
                'url' => 'https://wedontknow',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/mysteries',
                'isOnline' => false,
                'slug' => 'unknown-city-conference',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => '',
                ],
                'tags' => [
                    [
                        'name' => 'php',
                    ],
                ],
            ],
            [
                'expectedTag' => 'php',
                'expectedCity' => '',
            ],
        ];

        yield [
            [
                'name' => 'Jardin Zen Land',
                'url' => 'https://peaceandlove',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/innerpeace',
                'isOnline' => false,
                'slug' => 'i-need-a-break',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'Chaillé-sous-les-Ormeaux',
                ],
                'tags' => [
                    [
                        'name' => 'jardinage',
                    ],
                    [
                        'name' => 'zen',
                    ],
                ],
            ],
            [
                'expectedTag' => null,
                'expectedCity' => 'Chaillé-sous-les-Ormeaux',
            ],
        ];
    }

    public function testRealResponse()
    {
        $realTululaData = file_get_contents(\dirname(__DIR__).'/Fixtures/tulula.json');
        $response = new MockResponse($realTululaData);
        $result = $this
            ->createFetcher($response, [
                'expectedTag' => 'php',
                'expectedCity' => 'n/a',
            ])
            ->fetch();

        self::assertNotEmpty(iterator_to_array($result));
    }

    private function createFetcher(MockResponse $response, array $expectedItems): TululaFetcher
    {
        $locationGuesser = $this->prophesize(LocationGuesser::class);
        $locationGuesser
            ->getContinent(Argument::type('string'))
            ->willReturn($continent = new Continent());
        $continent->setName('Europe');
        $continent->setEnabled(true);

        $tagRepository = $this->prophesize(TagRepository::class);
        $tagRepository
            ->findTagByName(Argument::type('string'))
            ->willReturn($tag = new Tag());
        $tag->setName($expectedItems['expectedTag']);
        $tag->setSelected(true);

        $excludedTagRepository = $this->prophesize(ExcludedTagRepository::class);
        $excludedTagRepository
            ->findOneBy(Argument::type('array'))
            ->willReturn(null);
        $client = new MockHttpClient($response);

        $fetcher = new TululaFetcher(
            $locationGuesser->reveal(),
            $tagRepository->reveal(),
            $excludedTagRepository->reveal(),
            $client
        );

        return $fetcher;
    }
}
