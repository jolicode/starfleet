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
use App\Entity\ExcludedTag;
use App\Entity\Tag;
use Behat\Transliterator\Transliterator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ConfTechFetcher implements FetcherInterface
{
    const SOURCE = 'conf-tech';
    const TAGS_SYNONYMS = [
        'android',
        'css',
        'data',
        'devops',
        'dotnet',
        'elixir',
        'general',
        'golang',
        'graphql',
        'ios',
        'javascript',
        'php',
        'python',
        'ruby',
        'rust',
        'security',
        'tech-comm',
        'ux',
    ];

    private $em;
    private $conferenceRepository;
    private $httpClient;
    private $serializer;
    private $logger;
    private $tagRepository;
    private $excludedTags;
    private $locationGuesser;
    private $slugger;

    public function __construct(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        LocationGuesser $locationGuesser
    ) {
        $this->em = $doctrine->getManager();
        // @todo replace with proper DI when http-client will be released as stable
        $this->httpClient = HttpClient::create();
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
        $this->excludedTags = $this->em->getRepository(ExcludedTag::class)->findAll();
        $this->locationGuesser = $locationGuesser;
        $this->slugger = new AsciiSlugger();
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getUrl(array $params = []): string
    {
        return "https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/$params[date]/$params[tag].json";
    }

    public function fetch(): \Generator
    {
        foreach ([date('Y'), date('Y', strtotime('+1 year'))] as $date) {
            foreach ($this->tagRepository->findBy(['selected' => true]) as $tag) {
                $tagKey = array_search($this->slugger->slug(strtolower($tag->getName()))->toString(), self::TAGS_SYNONYMS);

                if (false === $tagKey) {
                    continue;
                }

                $tagSynonym = self::TAGS_SYNONYMS[$tagKey];
                $url = $this->getUrl(['date' => $date, 'tag' => $tagSynonym]);

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
                    $this->logger->error('Source URL returns 404', [
                        'url' => $url,
                        'source' => self::SOURCE,
                    ]);
                    continue;
                }

                $data = json_decode($response->getContent(), true);

                yield $url => $this->denormalizeConferences($data, $tag);
            }
        }
    }

    public function denormalizeConferences(array $rawConferences, Tag $tag): array
    {
        $conferences = [];

        foreach ($rawConferences as $rawConference) {
            $query = sprintf('%s %s', $rawConference['city'], 'U.S.A.' === $rawConference['country'] ? 'United States of America' : $rawConference['country']);
            $continent = $this->locationGuesser->getContinent($query);
            $country = $this->locationGuesser->getCountry($query);

            $startDate = \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $rawConference['startDate'].' 00:00:00');

            if (!$continent instanceof Continent || !$continent->getEnabled()) {
                continue;
            }

            // In case of invalid startDate, we skip the conference. It will be handled again later.
            if (!$startDate) {
                continue;
            }

            $slug = Transliterator::transliterate(sprintf('%s %s %s', $rawConference['name'], $rawConference['city'], $startDate->format('Y')));

            $conference = new Conference();
            $conference->setSource(self::SOURCE);
            $conference->setSlug($slug);
            $conference->setName($rawConference['name']);
            $conference->setCity($rawConference['city']);
            $conference->setCountry($country);
            $conference->setStartAt($startDate);
            $conference->setSiteUrl($rawConference['url']);
            $conference->addTag($tag);

            $excluded = false;
            foreach ($this->excludedTags as $excludedTag) {
                if (fnmatch($excludedTag->getName(), $rawConference['name'], FNM_CASEFOLD)) {
                    $excluded = true;
                    break;
                }
            }
            $conference->setExcluded($excluded);

            if (\array_key_exists('endDate', $rawConference)) {
                $endDate = \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $rawConference['endDate'].' 00:00:00');
                $conference->setEndAt($endDate);
            }

            if (\array_key_exists('description', $rawConference)) {
                $conference->setDescription($rawConference['description']);
            }

            if (\array_key_exists('cfpUrl', $rawConference)) {
                $conference->setCfpUrl($rawConference['cfpUrl']);
            }

            if (\array_key_exists('cfpEndDate', $rawConference)) {
                $cfpEndAt = \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $rawConference['cfpEndDate'].' 00:00:00');
                $conference->setCfpEndAt($cfpEndAt);
            }

            $conferences[] = $conference;
        }

        return $conferences;
    }
}
