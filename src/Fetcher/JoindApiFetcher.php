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
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class JoindApiFetcher implements FetcherInterface
{
    const SOURCE = 'joind';
    const TAGS_SYNONYMS = [
        'android',
        'css',
        'data',
        'devops',
        'dotnet',
        'elixir',
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
    ];

    private $em;
    private $conferenceRepository;
    private $httpClient;
    private $serializer;
    private $logger;
    private $tagRepository;
    private $locationGuesser;
    private $slugger;

    public function __construct(ManagerRegistry $doctrine, SerializerInterface $serializer, LoggerInterface $logger, LocationGuesser $locationGuesser)
    {
        $this->em = $doctrine->getManager();
        // @todo replace with proper DI when http-client will be released as stable
        $this->httpClient = HttpClient::create();
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
        $this->locationGuesser = $locationGuesser;
        $this->slugger = new AsciiSlugger();
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getUrl(array $params = []): string
    {
        return 'https://api.joind.in/v2.1/events?verbose=yes&resultsperpage=20&startdate='.$params['startdate'].'&tags[]='.$params['tag'];
    }

    public function fetch(): \Generator
    {
        foreach ($this->tagRepository->findBy(['selected' => true]) as $tag) {
            $tagKey = array_search($this->slugger->slug(strtolower($tag->getName()))->toString(), self::TAGS_SYNONYMS);

            if (false === $tagKey) {
                continue;
            }

            $tagSynonym = self::TAGS_SYNONYMS[$tagKey];
            $url = $this->getUrl(['startdate' => date('Y'), 'tag' => $tagSynonym]);

            try {
                $response = $this->httpClient->request('GET', $url);
            } catch (TransportExceptionInterface $exception) {
                $this->logger->error('HttpClient Transport Exception', [
                    'url' => $url,
                    'source' => self::SOURCE,
                    'exception' => $exception->getMessage(),
                ]);
                continue;
            }

            if (404 === $response->getStatusCode()) {
                $this->logger->error('Source URL returns 404', ['url' => $this->getUrl(['startdate' => date('Y'), 'tag' => $tagSynonym]), 'source' => self::SOURCE]);
                continue;
            }

            $data = $response->toArray();

            if (0 === $data['meta']['total']) {
                continue;
            }

            foreach ($data['events'] as $rawConference) {
                yield $url => $this->denormalizeConference($rawConference, $tag);
            }
        }
    }

    public function denormalizeConference(array $rawConference, Tag $tag): ?Conference
    {
        $city = str_ireplace('_', ' ', $rawConference['tz_place']);
        $query = sprintf('%s', $city);
        $continent = $this->locationGuesser->getContinent($query);
        $country = $this->locationGuesser->getCountry($query);

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
        $conference->addTag($tag);

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
}
