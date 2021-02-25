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
use App\Fetcher\JoindApiFetcher;
use App\Fetcher\LocationGuesser;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class JoindApiFetcherTest extends KernelTestCase
{
    /**
     * @dataProvider provideConferences
     */
    public function testFetch(array $rawConference, array $expectedItems, array $fetcherConfig = [])
    {
        $data['events'][] = $rawConference;

        $response = new MockResponse(json_encode($data));
        $result = $this->createFetcher($response)->fetch($fetcherConfig);

        if (!$expectedItems['expectedCity']) {
            return self::assertEmpty(iterator_to_array($result));
        }

        foreach ($result as $fetchedConference) {
            self::assertSame($rawConference['name'], $fetchedConference->getName());
            self::assertSame($rawConference['href'], $fetchedConference->getSiteUrl());
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['start_date'])->format('Y-m-d') === $fetchedConference->getStartAt()->format('Y-m-d'));
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['end_date'])->format('Y-m-d') === $fetchedConference->getEndAt()->format('Y-m-d'));
            self::assertTrue(\DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['cfp_end_date'])->format('Y-m-d') === $fetchedConference->getCfpEndAt()->format('Y-m-d'));
            self::assertSame($rawConference['cfp_url'], $fetchedConference->getCfpUrl());
            self::assertSame($rawConference['url_friendly_name'], $fetchedConference->getSlug());
            self::assertSame($expectedItems['expectedCity'], $fetchedConference->getCity());

            foreach ($rawConference['tags'] as $tag) {
                self::assertTrue(\in_array($tag, $fetchedConference->getTags()));
            }
        }
    }

    public function provideConferences(): \Generator
    {
        yield 'Test normal Conference is correctly denormalized' => [
            [
                'name' => 'Normal Conference',
                'href' => 'https://php',
                'start_date' => '2000-01-15',
                'end_date' => '2000-01-17',
                'cfp_end_date' => '2000-01-01',
                'cfp_url' => '/php',
                'url_friendly_name' => 'php-tour-2000',
                'tz_place' => 'Maubeuge',
                'location' => 'Place de l\'église',
                'tags' => [
                    'name' => 'php',
                ],
            ],
            [
                'expectedCity' => 'Maubeuge',
            ],
            [
                'allowEmptyTags' => false,
                'tags' => ['php'],
            ],
        ];

        yield 'Test online Conference is correctly denormalized' => [
            [
                'name' => 'Online Conference',
                'href' => 'https://php',
                'start_date' => '2000-01-15',
                'end_date' => '2000-01-17',
                'cfp_end_date' => '2000-01-01',
                'cfp_url' => '/php',
                'url_friendly_name' => 'php-tour-2000',
                'tz_place' => 'Maubeuge',
                'location' => 'OnlINe',
                'tags' => [
                    'name' => 'php',
                ],
            ],
            [
                'expectedCity' => 'Online',
            ],
            [
                'allowEmptyTags' => false,
                'tags' => ['php', 'symfony', 'devops'],
            ],
        ];

        yield 'Test underscore is correctly replaced by space' => [
            [
                'name' => 'Conference with underscore in its name',
                'href' => 'https://php',
                'start_date' => '2000-01-15',
                'end_date' => '2000-01-17',
                'cfp_end_date' => '2000-01-01',
                'cfp_url' => '/php',
                'url_friendly_name' => 'php-tour-2000',
                'tz_place' => 'Unbelievable_maubeuge_City',
                'location' => 'Place de l\'église',
                'tags' => [
                    'name' => 'php',
                ],
            ],
            [
                'expectedCity' => 'Unbelievable Maubeuge City',
            ],
            [
                'allowEmptyTags' => false,
                'tags' => ['php'],
            ],
        ];

        yield 'Test not configured fetcher doesn\'t fetch anything' => [
            [
                'name' => 'The fetcher is not configured',
                'href' => 'https://it-should-return-nothing.com',
                'start_date' => '2000-01-15',
                'end_date' => '2000-01-17',
                'cfp_end_date' => '2000-01-01',
                'cfp_url' => '/it-fetches-nothing',
                'url_friendly_name' => 'i-dont-exist',
                'tz_place' => 'Maubeuge',
                'location' => 'Place de l\'église',
                'tags' => [],
            ],
            [
                'expectedCity' => null,
            ],
            [],
        ];
    }

    public function testRealResponse()
    {
        $realJoindData = file_get_contents(\dirname(__DIR__).'/Fixtures/joind.json');
        $response = new MockResponse($realJoindData);
        $result = $this
            ->createFetcher($response)
            ->fetch([
                'tags' => [
                    'php',
                    'css',
                    'javascript',
                    'java',
                    'python',
                ],
                'allowEmptyTags' => false,
            ]);

        self::assertNotEmpty(iterator_to_array($result));
    }

    private function createFetcher(MockResponse $response): JoindApiFetcher
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

        $locationGuesser
            ->getCountry(Argument::type('string'))
            ->willReturn('FR');

        $client = new MockHttpClient($response);

        $fetcher = new JoindApiFetcher(
            $locationGuesser->reveal(),
            $client
        );

        return $fetcher;
    }
}
