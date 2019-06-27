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
use App\Entity\Tag;
use App\Enum\TagEnum;
use Behat\Transliterator\Transliterator;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

class ConfTechFetcher implements FetcherInterface
{
    use HashConferenceTrait;

    const SOURCE = 'conf-tech';
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
        null, //9
        null, //10
        'general', //11
        'golang', //12
        null, //13
        'graphql', //14
        null, //15
        'ios', //16
        null, //17
        'javascript', //18
        null, //19
        null, //20
        'php', //21
        'python', //22
        null, //23
        'ruby', //24
        'rust', //25
        null, //26
        'security', //27
        'tech-comm', //28
        'ux', //29
    ];

    private $em;
    private $conferenceRepository;
    private $httpClient;
    private $serializer;
    private $logger;
    private $tagRepository;

    public function __construct(RegistryInterface $doctrine, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        // @todo replace with proper DI when http-client will be released as stable
        $this->httpClient = HttpClient::create();
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getUrl(array $params = []): string
    {
        return "https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/$params[date]/$params[tag].json";
    }

    public function fetch(): array
    {
        $conferences = [];

        foreach ([date('Y'), date('Y', strtotime('+1 year'))] as $date) {
            // @todo: use enabled tags instead of all tags from enum
            foreach (array_combine(TagEnum::toArray(), self::TAGS_SYNONYMS) as $tagName => $tagSynonym) {
                if (null === $tagSynonym) {
                    continue;
                }

                $response = $this->httpClient->request('GET', $this->getUrl(['date' => $date, 'tag' => $tagSynonym]));

                if (404 === $response->getStatusCode()) {
                    $this->logger->error('Source URL returns 404', ['url' => $this->getUrl(), 'source' => self::SOURCE]);
                    continue;
                }

                $tag = $this->tagRepository->findOneBy([
                    'name' => $tagName,
                    'selected' => true,
                ]);

                if (!$tag instanceof Tag) {
                    continue;
                }

                $data = json_decode($response->getContent(), true);
                $fetchedConferences = $this->denormalizeConferences($data, self::SOURCE, $tag);
                $conferences = array_merge($conferences, iterator_to_array($fetchedConferences));
            }
        }

        return $conferences;
    }

    public function denormalizeConferences(array $rawConferences, string $source, Tag $tag): \Generator
    {
        foreach ($rawConferences as $rawConference) {
            $startDate = \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $rawConference['startDate'].' 00:00:00');

            // In case of invalid startDate, we skip the conference. It will be handled again later.
            if (!$startDate) {
                continue;
            }

            $hash = $this->hash($rawConference['name'], $rawConference['url'], $startDate);
            $slug = Transliterator::transliterate(sprintf('%s %s %s', $rawConference['name'], $rawConference['city'], $startDate->format('Y')));

            $conference = new Conference();
            $conference->setSource($source);
            $conference->setHash($hash);
            $conference->setSlug($slug);
            $conference->setName($rawConference['name']);
            $conference->setLocation($rawConference['city']);
            $conference->setStartAt($startDate);
            $conference->setSiteUrl($rawConference['url']);
            $conference->addTag($tag);

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

            yield $hash => $conference;
        }
    }
}
