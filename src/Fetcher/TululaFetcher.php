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

use App\Entity\Conference;
use App\Entity\Continent;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TululaFetcher implements FetcherInterface
{
    private const SOURCE = 'tulula';
    private const TULULA_URL = 'https://tulu.la/api/public';
    private const TAG_ALLOW_LIST = [
        'android',
        'css',
        'data',
        'devops',
        'dotnet',
        'facebook',
        'golang',
        'html',
        'ios',
        'java',
        'javascript',
        'nodejs',
        'php',
        'python',
        'react native',
        'ruby',
        'rust',
        'security',
        'ux',
        'ai/ml',
        'sql',
        'iot',
    ];

    private LocationGuesser $locationGuesser;
    private TagRepository $tagRepository;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(LocationGuesser $locationGuesser, TagRepository $tagRepository, ?HttpClientInterface $client = null, ?LoggerInterface $logger = null)
    {
        $this->locationGuesser = $locationGuesser;
        $this->tagRepository = $tagRepository;
        $this->client = $client ?: HttpClient::create();
        $this->logger = $logger ?: new NullLogger();
    }

    public function isActive(): bool
    {
        return true;
    }

    public function fetch(): \Generator
    {
        $tags = [];

        foreach ($this->queryTululaEvents() as $conference) {
            foreach ($conference['tags'] as $tagName) {
                $tagName = strtolower($tagName['name']);

                if (\in_array($tagName, self::TAG_ALLOW_LIST)) {
                    if (!\array_key_exists($tagName, $tags)) {
                        $tag = $this->tagRepository->findTagByName($tagName);
                        $tags[$tagName] = $tag;
                    } else {
                        $tag = $tags[$tagName];
                    }

                    if ($tag && $tag->isSelected()) {
                        yield $this->denormalizeConference($conference, $tags[$tagName]);
                        break;
                    }
                }
            }
        }
    }

    private function denormalizeConference(array $rawConference, Tag $tag): ?Conference
    {
        $city = null;
        if (!$rawConference['isOnline']) {
            if ($city = $rawConference['venue']['city'] ?: $rawConference['venue']['state']) {
                $continent = $this->locationGuesser->getContinent($city);

                if (!$continent instanceof Continent || !$continent->getEnabled()) {
                    return null;
                }
            }
        }

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['dateStart']);
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['dateEnd']);
        $cfpEndDate = \DateTimeImmutable::createFromFormat('Y-m-d', $rawConference['cfpDateEnd']);

        // In case of invalid startDate, we skip the conference. It will be handled again later.
        if (!$startDate) {
            return null;
        }

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($rawConference['slug']);
        $conference->setName($rawConference['name']);
        $conference->setStartAt($startDate);
        $conference->setEndAt($endDate);
        $conference->setCfpEndAt($cfpEndDate);
        $conference->setSiteUrl($rawConference['url']);
        $conference->addTag($tag);
        $conference->setCfpUrl($rawConference['cfpUrl']);

        if ($rawConference['isOnline']) {
            $conference->setCity('Online');
            $conference->setOnline(true);
        } else {
            $conference->setCity($city);
            $conference->setCountry($rawConference['venue']['countryCode']);
        }

        return $conference;
    }

    private function queryTululaEvents(): ?array
    {
        try {
            $response = $this->client->request('POST', self::TULULA_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'variables' => [
                        'filter' => [
                            'cfpFrom' => date('Y-m-d'),
                            'cfpIsActive' => true,
                        ],
                    ],
                    'query' => <<<QUERY
                        query QueryEventsSearch(\$filter: EventSearchFilter) {
                            events: eventsSearch(filter: \$filter) {
                                events {
                                ...EventData
                                }
                            }
                        }
                        fragment EventData on Event {
                            name
                            url
                            dateStart
                            dateEnd
                            cfpDateEnd
                            cfpUrl
                            isOnline
                            slug
                            venue {
                                ...VenueData
                            }
                            tags {
                                ...TagData
                            }
                        }
                        fragment VenueData on Venue {
                            countryCode
                            state
                            city
                        }
                        fragment TagData on Tag {
                            name
                        }
                    QUERY
                ],
            ]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('HttpClient Transport Exception.', [
                'url' => self::TULULA_URL,
                'source' => self::SOURCE,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }

        if (200 !== $statusCode = $response->getStatusCode()) {
            $this->logger->error(sprintf('Source URL returns %d.', $statusCode), [
                'url' => self::TULULA_URL,
                'source' => self::SOURCE,
            ]);

            return null;
        }
        $result = $response->toArray();

        return $result['data']['events']['events'];
    }
}
