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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TululaFetcher implements FetcherInterface
{
    private const SOURCE = 'tulula';
    private const TULULA_URL = 'https://tulu.la/api/public';

    // These are all the tags available for fetching from Tulula.
    // If you feel like one is missing, which is very likely because there are many, you could add one by running the command, dumping the results and adding the missing tag to this list.
    private const SOURCE_AVAILABLE_TAGS = [
        'AI/ML',
        'AR/VR',
        'aws',
        'Azure',
        'Big Data',
        'analytics',
        'Android',
        'api',
        'architecture',
        'Big Data',
        'bootcamp',
        'chaos engineering',
        'CI/CD',
        'Cloud',
        'codecamp',
        'community',
        'containers',
        'cms',
        'CSS',
        'data',
        'Database',
        'data science',
        'developers',
        'DevOps',
        'Django',
        'Docker',
        'event sourcing',
        'Flask',
        'free',
        'frontend',
        'game develoment',
        'general',
        'Go',
        'GraphQL',
        'infrastructure',
        'innovation',
        'integration',
        'IoS',
        'IoT',
        'Java',
        'JavaScript',
        'jvm',
        'Kotlin',
        'Kubernetes',
        'microservices',
        'Microsoft',
        'mobile',
        'monitoring',
        'non-technical skills',
        'Open Source',
        'performance',
        'PHP',
        'programing',
        'prometheus',
        'pwa',
        'Python',
        'qa testing',
        'React',
        'react native',
        'resiliency engineering',
        'Robotics',
        'rpa',
        'Security',
        'serverless',
        'service mesh',
        'sql',
        'software',
        'software engineering',
        'sre',
        'Swift',
        'systems performance',
        'technology',
        'Ux design',
        'Web',
        'women',
        'women in tech',
    ];

    private LocationGuesser $locationGuesser;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(LocationGuesser $locationGuesser, ?HttpClientInterface $client = null, ?LoggerInterface $logger = null)
    {
        $this->locationGuesser = $locationGuesser;
        $this->client = $client ?: HttpClient::create();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param array<mixed> $configuration
     *
     * @return \Generator<Conference>
     */
    public function fetch(array $configuration = []): \Generator
    {
        if (0 === \count($configuration) || 0 === \count($configuration['tags'])) {
            $this->logger->warning(sprintf('The %s is not configured and will not fetch anything. Please add a configuration in the admin.', self::class));

            return;
        }

        foreach ($this->queryTulula() as $conference) {
            if (0 === \count($conference['tags'])) {
                // Sometimes, an event will have no tags. If you want to fetch them anyway, you should set the `allowEmptyTags` option to true in the admin
                if (\array_key_exists('allowEmptyTags', $configuration) && $configuration['allowEmptyTags']) {
                    yield $this->denormalizeConference($conference);
                    continue;
                }
                continue;
            }

            foreach ($conference['tags'] as $tag) {
                if (\in_array($tag['name'], $configuration['tags'])) {
                    $denormalizedConference = $this->denormalizeConference($conference);

                    if (!$denormalizedConference) {
                        continue;
                    }

                    yield $denormalizedConference;
                    break;
                }
            }
        }
    }

    public function configureForm(FormBuilderInterface $formBuilder): void
    {
        $formBuilder
            ->add('tags', ChoiceType::class, [
                'label' => 'Tags',
                'choices' => array_combine(self::SOURCE_AVAILABLE_TAGS, self::SOURCE_AVAILABLE_TAGS),
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ])
            ->add('allowEmptyTags', CheckboxType::class, [
                'label' => 'Allow Empty Tags',
                'help' => 'Fetch conferences with no tags at all',
                'required' => false,
            ]);
    }

    /** @param array<mixed> $rawConference */
    private function denormalizeConference(array $rawConference): ?Conference
    {
        $city = null;
        $coords = null;
        if (!$rawConference['isOnline']) {
            if ($city = $rawConference['venue']['city'] ?: $rawConference['venue']['state']) {
                $continent = $this->locationGuesser->getContinent($city);
                $coords = $this->locationGuesser->getCoordinates($city);

                if (!$continent instanceof Continent || !$continent->getEnabled()) {
                    return null;
                }
            }
        }

        $startDate = new \DateTimeImmutable($rawConference['dateStart']);
        $endDate = new \DateTimeImmutable($rawConference['dateEnd']);
        $cfpEndDate = new \DateTimeImmutable($rawConference['cfpDateEnd']);

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($rawConference['slug']);
        $conference->setName($rawConference['name']);
        $conference->setStartAt($startDate);
        $conference->setEndAt($endDate);
        $conference->setCfpEndAt($cfpEndDate);
        $conference->setSiteUrl($rawConference['url']);
        $conference->setCfpUrl($rawConference['cfpUrl']);

        foreach ($rawConference['tags'] as $tag) {
            $conference->addTag($tag['name']);
        }

        if ($rawConference['isOnline']) {
            $conference->setCity('Online');
            $conference->setOnline(true);
        } else {
            $conference->setCity($city);
            $conference->setCountry($rawConference['venue']['countryCode']);
            $conference->setCoordinates($coords);
        }

        return $conference;
    }

    /** @return array<array>|null */
    private function queryTulula(): ?array
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
