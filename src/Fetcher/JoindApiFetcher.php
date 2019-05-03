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
use Gedmo\Sluggable\Util\Urlizer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class JoindApiFetcher implements FetcherInterface
{
    use HashConferenceTrait;

    const SOURCE = 'joind';

    private $logger;
    private $em;
    private $tagRepository;
    private $conferenceRepository;

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
    }

    public function getUrl($params = []): string
    {
        return 'https://api.joind.in/v2.1/events?filter=cfp&tags='.$params['tag'];
    }

    public function fetch(): array
    {
        $params = $this->matchTags();

        $allNewConferences = [];
        $conferences = [];
        $client = new Client();

        foreach ($params as $key => $technologie) {
            $allNewConferences[$technologie[1]] = [];
            try {
                $response = $client->request('GET', $this->getUrl(['tag' => $technologie[0]]));
                $fetchedConferences = json_decode($response->getBody());
            } catch (GuzzleException $e) {
                if (400 === $e->getCode()) {
                    $this->logger->error($e->getMessage());
                } else {
                    $this->logger->error($e->getMessage());
                    throw new Exception($e);
                }
            }

            $source = self::SOURCE;
            $fetchedConferences = $fetchedConferences->events;
            $conferences = $this->confToArray($fetchedConferences, $source, $technologie, $conferences);
        }

        return $conferences;
    }

    private function confToArray(array $fetchedConferences, $source, $technologie, $conferences = [])
    {
        $tag = $this->tagRepository->getTagByName($technologie[1]);

        foreach ($fetchedConferences as $fC) {
            $this->hash($fC);
            $fC->tag = $tag;
            $fC->source = $source;

            $conference = $this->hydrateConference($fC);

            $conferences[$conference->getHash()] = $conference;
        }

        return $conferences;
    }

    private function matchTags()
    {
        $tagsSelected = $this->tagRepository->getTagsBySelected();

        $confTechFetcherSynonyms = [
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

        $tagMatch = array_combine(TagEnum::toArray(), $confTechFetcherSynonyms);

        $params = [
        ];

        foreach ($tagsSelected as $tag) {
            /* @var Tag $tag*/
            if (null !== $tagMatch[$tag->getName()]) {
                array_push($params, [$tagMatch[$tag->getName()], $tag->getName()]);
            }
        }

        return $params;
    }

    public function getLocation($conference)
    {
        $location = $conference->tz_place;

        return $location;
    }

    private function hydrateConference($fC)
    {
        $conference = new Conference();
        $conference->setSource($fC->source);
        $conference->setHash($fC->hash);
        $conference->setSlug($fC->slug);
        $conference->setName($fC->name);
        $conference->setLocation($this->getLocation($fC));
        $conference->setStartAt(\DateTime::createFromFormat('Y-m-d\TH:i:sT', $fC->start_date));
        $conference->setEndAt($fC->endAt);
        $conference->setSiteUrl($fC->href);
        $conference->addTag($fC->tag);

        if (isset($fC->description)) {
            $conference->setDescription($fC->description);
        }

        if (isset($fC->uri)) {
            $conference->setCfpUrl($fC->uri);
        }

        if (isset($fC->cfpEndDate)) {
            $cfpEndAt = \DateTime::createFromFormat('Y-m-d', $fC->cfpEndDate);
            $conference->setCfpEndAt($cfpEndAt);
        }

        return $conference;
    }

    public function denormalizeConferences(array $rawConferences, string $source, string $tagName): \Generator
    {
        // TODO: Implement denormalizeConferences() method.
    }
}
