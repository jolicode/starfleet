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
use App\Enum\TagEnum;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

class JoindApiFetcher implements FetcherInterface
{
    use HashConferenceTrait;

    const SOURCE = 'joind';
    // Use to match source topics with Starfleet Tags
    const TAGS_SYNONYMS = [
        'android', //0
        null, //1
        null, //2
        'css', //3
        null, //4
        'data', //5
        'devops', //6
        'dotnet', //7
        'elixir', //8
        'facebook', //9
        null, //10
        null, //11
        'golang', //12
        null, //13
        null, //14
        'html', //15
        'ios', //16
        'java', //17
        'javascript', //18
        null, //19
        'nodejs', //20
        'php', //21
        'python', //22
        'react native', //23
        'ruby', //24
        'rust', //25
        null, //26
        'security', //27
        null, //28
        'ux', //29
    ];

    private $em;
    private $conferenceRepository;
    private $httpClient;
    private $serializer;
    private $logger;
    private $tagRepository;
    private $excludedTags;
    private $continentGuesser;

    public function __construct(ManagerRegistry $doctrine, SerializerInterface $serializer, LoggerInterface $logger, ContinentGuesser $continentGuesser)
    {
        $this->em = $doctrine->getManager();
        // @todo replace with proper DI when http-client will be released as stable
        $this->httpClient = HttpClient::create();
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
        $this->excludedTags = $this->em->getRepository(ExcludedTag::class)->findAll();
        $this->continentGuesser = $continentGuesser;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getUrl(array $params = []): string
    {
        return 'https://api.joind.in/v2.1/events?verbose=yes&resultsperpage=20&startdate='.$params['startdate'].'&tags[]='.$params['tag'];
    }

    public function fetch(): array
    {
        $conferences = [];

        // @todo: use enabled tags instead of all tags from enum
        foreach (array_combine(TagEnum::toArray(), self::TAGS_SYNONYMS) as $tagName => $tagSynonym) {
            if (null === $tagSynonym) {
                continue;
            }

            $response = $this->httpClient->request('GET', $this->getUrl(['startdate' => date('Y'), 'tag' => $tagSynonym]));

            if (404 === $response->getStatusCode()) {
                $this->logger->error('Source URL returns 404', ['url' => $this->getUrl(), 'source' => self::SOURCE]);
                continue;
            }

            $data = json_decode($response->getContent(), true);

            if (0 === $data['meta']['total']) {
                continue;
            }

            $tag = $this->tagRepository->findOneBy([
                'name' => $tagName,
                'selected' => true,
            ]);

            if (!$tag instanceof Tag) {
                continue;
            }

            $fetchedConferences = $this->denormalizeConferences($data['events'], self::SOURCE, $tag);

            $conferences = array_merge($conferences, iterator_to_array($fetchedConferences));
        }

        return $conferences;
    }

    public function denormalizeConferences(array $rawConferences, string $source, Tag $tag): \Generator
    {
        foreach ($rawConferences as $rawConference) {
            $city = str_ireplace('_', ' ', $rawConference['tz_place']);
            $query = sprintf('%s', $city);
            $continent = $this->continentGuesser->getContinent($query);

            if (!$continent->getEnabled() || !$continent instanceof Continent) {
                continue;
            }

            $startDate = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $rawConference['start_date']);

            // In case of invalid startDate, we skip the conference. It will be handled again later.
            if (!$startDate) {
                continue;
            }

            $hash = $this->hash($rawConference['name'], $rawConference['href'], $startDate);
            $slug = $rawConference['url_friendly_name'];

            $conference = new Conference();
            $conference->setSource($source);
            $conference->setHash($hash);
            $conference->setSlug($slug);
            $conference->setName($rawConference['name']);
            $conference->setLocation($city);
            $conference->setStartAt($startDate);
            $conference->setSiteUrl($rawConference['href']);
            $conference->addTag($tag);

            $excluded = false;
            foreach ($this->excludedTags as $excludedTag) {
                if (fnmatch($excludedTag->getName(), $rawConference['name'], FNM_CASEFOLD)) {
                    $excluded = true;
                    break;
                }
            }
            $conference->setExcluded($excluded);
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

            yield $hash => $conference;
        }
    }
}
