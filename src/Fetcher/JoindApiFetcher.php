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

class JoindApiFetcher implements FetcherInterface
{
    private const SOURCE = 'joind';
    // The API provides with the possibility of filtering only the events with an active CfP, but since conferences are sometimes annouced before their CfP is open, we don't use it
    private const JOINDIN_URL = 'https://api.joind.in/v2.1/events?verbose=yes&filter=upcoming';

    // These are all the tags available for fetching from Joind.In
    // If you feel like one is missing, which is very likely because there are many, feel free to add one from https://api.joind.in/v2.1/events?verbose=yes?resultsperpage=100
    private const SOURCE_AVAILABLE_TAGS = [
        'accessibility',
        'afup',
        'ai',
        'android',
        'angular',
        'angularday',
        'bootstrap',
        'community',
        'composer',
        'composer2',
        'css',
        'data',
        'design systems',
        'dev',
        'devops',
        'dotnet',
        'ecommberlin',
        'ecommerce',
        'elixir',
        'facebook',
        'frontend',
        'golang',
        'grusp',
        'html',
        'infection',
        'ios',
        'iot',
        'java',
        'javascript',
        'laravel',
        'logistics',
        'marketing',
        'marketing automation',
        'mautic',
        'mauticon',
        'meetup',
        'nodejs',
        'ohdear',
        'ohdearapp',
        'open source',
        'php',
        'phpbenelux',
        'phpsw',
        'php8',
        'python',
        'reactphp',
        'react native',
        'retail',
        'ruby',
        'rust',
        'security',
        'symfony',
        'tailwind',
        'ui',
        'ux',
        'variable fonts',
        'web dev',
    ];

    private LoggerInterface $logger;

    public function __construct(
        private LocationGuesser $locationGuesser,
        private ?HttpClientInterface $client = null,
        ?LoggerInterface $logger = null,
    ) {
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

        $url = self::JOINDIN_URL;

        // Sometimes, an event will have no tags. If you want to fetch them anyway, you should set the `allowEmptyTags` option to true in the admin
        if (\array_key_exists('allowEmptyTags', $configuration) && $configuration['allowEmptyTags']) {
            foreach ($this->queryJoindIn($url . '&tags[]=') as $conference) {
                yield $this->denormalizeConference($conference);
            }
        }

        foreach ($configuration['tags'] as $tag) {
            $url .= sprintf('&tags[]=%s', $tag);
        }

        foreach ($this->queryJoindIn($url) as $conference) {
            $denormalizedConference = $this->denormalizeConference($conference);

            if (!$denormalizedConference) {
                continue;
            }

            yield $denormalizedConference;
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
            ])
        ;
    }

    /** @param array<mixed> $rawConference */
    public function denormalizeConference(array $rawConference): ?Conference
    {
        $city = ucwords(str_ireplace('_', ' ', $rawConference['tz_place']));
        $continent = $this->locationGuesser->getContinent($city);
        $country = $this->locationGuesser->getCountry($city);
        $coords = $this->locationGuesser->getCoordinates($city);

        if (!$continent instanceof Continent || !$continent->getEnabled()) {
            return null;
        }

        $startDate = new \DateTimeImmutable($rawConference['start_date']);

        $slug = $rawConference['url_friendly_name'];

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($slug);
        $conference->setStartAt($startDate);
        $name = trim(str_replace($startDate->format('Y'), '', $rawConference['name']));
        $conference->setName($name);
        $conference->setSiteUrl($rawConference['href']);

        $lowerLocation = strtolower($rawConference['location']);
        if ('online' === $lowerLocation || 'online conference' === $lowerLocation) {
            $conference->setOnline(true);
            $conference->setCity('Online');
        } else {
            $conference->setCity($city);
            $conference->setCountry($country);
            $conference->setCoordinates($coords);
        }

        foreach ($rawConference['tags'] as $tag) {
            $conference->addTag($tag);
        }

        if (\array_key_exists('end_date', $rawConference) && $rawConference['end_date']) {
            $endDate = new \DateTimeImmutable($rawConference['end_date']);
            $conference->setEndAt($endDate);
        }

        if (\array_key_exists('description', $rawConference) && $rawConference['description']) {
            $conference->setDescription($rawConference['description']);
        }

        if (\array_key_exists('cfp_url', $rawConference) && $rawConference['cfp_url']) {
            $conference->setCfpUrl($rawConference['cfp_url']);
        }

        if (\array_key_exists('cfp_end_date', $rawConference) && $rawConference['cfp_end_date']) {
            $cfpEndAt = new \DateTimeImmutable($rawConference['cfp_end_date']);
            $conference->setCfpEndAt($cfpEndAt);
        }

        return $conference;
    }

    /** @return null|array<array> */
    private function queryJoindIn(string $url): ?array
    {
        try {
            $response = $this->client->request('GET', $url);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('HttpClient Transport Exception', [
                'url' => $url,
                'source' => self::SOURCE,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }

        if (200 !== $statusCode = $response->getStatusCode()) {
            $this->logger->error(sprintf('Source URL returns %d', $statusCode), [
                'url' => $url,
                'source' => self::SOURCE,
            ]);

            return null;
        }

        $result = $response->toArray();

        return $result['events'];
    }
}
