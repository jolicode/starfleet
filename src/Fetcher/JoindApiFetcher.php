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

    private LocationGuesser $locationGuesser;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(LocationGuesser $locationGuesser, ?HttpClientInterface $client = null, ?LoggerInterface $logger)
    {
        $this->locationGuesser = $locationGuesser;
        $this->client = $client ?: HttpClient::create();
        $this->logger = $logger ?: new NullLogger();
    }

    public function fetch(array $configuration = []): ?\Generator
    {
        if (0 === \count($configuration) || 0 === \count($configuration['tags'])) {
            $this->logger->warning(sprintf('The %s is not configured and will not fetch anything. Please add a configuration in the admin.', self::class));

            return;
        }

        $url = self::JOINDIN_URL;

        // Sometimes, an event will have no tags. If you want to fetch them anyway, you should set the `allowEmptyTags` option to true in the admin
        if ($configuration['allowEmptyTags']) {
            foreach ($this->queryJoindIn($url.'&tags[]=') as $conference) {
                yield $this->denormalizeConference($conference);
            }
        }

        foreach ($configuration['tags'] as $tag) {
            $url .= sprintf('&tags[]=%s', $tag);
        }

        foreach ($this->queryJoindIn($url) as $conference) {
            yield $this->denormalizeConference($conference);
        }
    }

    public function configureForm(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder
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

    public function denormalizeConference(array $rawConference): ?Conference
    {
        $city = str_ireplace('_', ' ', $rawConference['tz_place']);
        $continent = $this->locationGuesser->getContinent($city);
        $country = $this->locationGuesser->getCountry($city);

        if (!$continent->getEnabled() || !$continent instanceof Continent) {
            return null;
        }

        $startDate = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $rawConference['start_date']);

        // In case of invalid startDate, we skip the conference. It will be handled again later.
        if (!$startDate) {
            return null;
        }

        $slug = $rawConference['url_friendly_name'];

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($slug);
        $conference->setName($rawConference['name']);
        $conference->setCity($city);
        $conference->setCountry($country);
        $conference->setStartAt($startDate);
        $conference->setSiteUrl($rawConference['href']);

        foreach ($rawConference['tags'] as $tag) {
            $conference->addTag($tag);
        }

        if ('online' === $city) {
            $conference->setOnline(true);
        }

        if ($rawConference['end_date']) {
            $endDate = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $rawConference['end_date']);
            $conference->setEndAt($endDate);
        }

        if ($rawConference['description']) {
            $conference->setDescription($rawConference['description']);
        }

        if ($rawConference['cfp_url']) {
            $conference->setCfpUrl($rawConference['cfp_url']);
        }

        if ($rawConference['cfp_end_date']) {
            $cfpEndAt = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $rawConference['cfp_end_date']);
            $conference->setCfpEndAt($cfpEndAt);
        }

        $conferences[] = $conference;

        return $conference;
    }

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
