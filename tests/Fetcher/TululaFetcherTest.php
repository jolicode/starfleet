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
use App\Fetcher\LocationGuesser;
use App\Fetcher\TululaFetcher;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TululaFetcherTest extends KernelTestCase
{
    /**
     * @dataProvider provideConferences
     */
    public function testFetch(array $rawConference, array $expectedItems, array $fetcherConfig = [])
    {
        $data['data']['events']['events'][] = $rawConference;

        $response = new MockResponse(json_encode($data));
        $result = $this->createFetcher($response)->fetch($fetcherConfig);

        if (null === $expectedItems['expectedTags']) {
            return self::assertEmpty(iterator_to_array($result));
        }

        foreach ($result as $fetchedConference) {
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
    }

    public function provideConferences(): \Generator
    {
        yield 'Test normal Conference is correctly denormalized' => [
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
                'expectedTags' => 'php',
                'expectedCity' => 'Maubeuge',
            ],
            [
                'tags' => ['php'],
            ],
        ];

        yield 'Test online Conference is correctly denormalized' => [
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
                'expectedTags' => 'php',
                'expectedCity' => 'Online',
            ],
            [
                'tags' => ['php'],
            ],
        ];

        yield 'Test state field is used if city field is empty' => [
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
                'expectedTags' => 'php',
                'expectedCity' => 'X-Files Land',
            ],
            [
                'tags' => ['php'],
            ],
        ];

        yield 'Test city is empty if no city is provided' => [
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
                'expectedTags' => 'php',
                'expectedCity' => '',
            ],
            [
                'tags' => ['php'],
            ],
        ];

        yield 'Test only Conferences selected tags are fetched' => [
            [
                'name' => 'Conf with no selected tags',
                'url' => 'https://not-interesting.com',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/idontcare.com',
                'isOnline' => false,
                'slug' => 'not-interrested-sorry',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'Chaillé-sous-les-Ormeaux',
                ],
                'tags' => [
                    [
                        'name' => 'not what I like',
                    ],
                    [
                        'name' => 'uninterresting stuff',
                    ],
                ],
            ],
            [
                'expectedTags' => null,
                'expectedCity' => 'Chaillé-sous-les-Ormeaux',
            ],
            [
                'tags' => ['php'],
            ],
        ];

        yield 'Test allowEmptyTags set to false works as intended' => [
            [
                'name' => 'Conf with no tags and allowEmptyTags is false',
                'url' => 'https://it-should-return-nothing.com',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/i-dont-want-empty-tags',
                'isOnline' => false,
                'slug' => 'i-am-not-here',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'no-tags-city',
                ],
                'tags' => [],
            ],
            [
                'expectedTags' => null,
                'expectedCity' => 'no-tags-city',
            ],
            [
                'tags' => ['php'],
                'allowEmptyTags' => false,
            ],
        ];

        yield 'Test allowEmptyTags set to true works as intended' => [
            [
                'name' => 'Conf with no tags and allowEmptyTags is true',
                'url' => 'https://it-should-return-the-conference.com',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/i-want-empty-tags',
                'isOnline' => false,
                'slug' => 'i-am-here',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'no-tags-city',
                ],
                'tags' => [],
            ],
            [
                'expectedTags' => [],
                'expectedCity' => 'no-tags-city',
            ],
            [
                'tags' => ['php'],
                'allowEmptyTags' => true,
            ],
        ];

        yield 'Test not configured fetcher doesn\'t fetch anything' => [
            [
                'name' => 'The fetcher doesn\'t have any config',
                'url' => 'https://it-should-return-nothing.com',
                'dateStart' => '2000-01-15',
                'dateEnd' => '2000-01-17',
                'cfpDateEnd' => '2000-01-01',
                'cfpUrl' => '/it-fetches-nothing',
                'isOnline' => false,
                'slug' => 'i-dont-exist',
                'venue' => [
                    'countryCode' => 'fr',
                    'state' => '',
                    'city' => 'no-configuration-fetcher-city',
                ],
                'tags' => [
                    [
                        'name' => 'php',
                    ],
                    [
                        'name' => 'symfony',
                    ],
                ],
            ],
            [
                'expectedTags' => null,
                'expectedCity' => 'no-configuration-fetcher-city',
            ],
            [],
        ];
    }

    public function testRealResponse()
    {
        $realTululaData = file_get_contents(\dirname(__DIR__).'/Fixtures/tulula.json');
        $response = new MockResponse($realTululaData);
        $result = $this
            ->createFetcher($response)
            ->fetch([
                'tags' => [
                    'PHP',
                    'CSS',
                    'JavaScript',
                    'Java',
                    'Python',
                ],
                'allowEmptyTags' => false,
            ]);

        self::assertNotEmpty(iterator_to_array($result));
    }

    private function createFetcher(MockResponse $response): TululaFetcher
    {
        $locationGuesser = $this->prophesize(LocationGuesser::class);
        $locationGuesser
            ->getContinent(Argument::type('string'))
            ->willReturn($continent = new Continent());
        $continent->setName('Europe');
        $continent->setEnabled(true);

        $locationGuesser
            ->getCoordinates(Argument::type('string'))
            ->willReturn([666, 666]);

        $client = new MockHttpClient($response);

        $fetcher = new TululaFetcher(
            $locationGuesser->reveal(),
            $client
        );

        return $fetcher;
    }
}
