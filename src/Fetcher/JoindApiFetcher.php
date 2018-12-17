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
    const SOURCE = 'joind';
    private $logger;
    private $em;
    private $tagRepository;
    private $conferenceRepository;

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->tagRepository = $this->em->getRepository(Tag::class);
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
    }

    public function getUrl($params = []): string
    {
        return 'https://api.joind.in/v2.1/events?filter=cfp&tags='.$params['tag'];
    }

    public function fetch(): array
    {
        $params = $this->matchTags();

        $client = new Client();

        $allNewConferences = [];
        $newConferencesCount = 0;

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
            $conferencesByTag = [];

            $fetchedConferences = $fetchedConferences->events;

            $conferencesByTag = $this->pushConf($fetchedConferences, $newConferencesCount, $source, $technologie, $conferencesByTag);

            $newConferencesCount = $conferencesByTag['newConferenceCount'];
            $allNewConferences[$technologie[1]] = $conferencesByTag['conferencesByTag'];
        }

        return [
            'conferences' => $allNewConferences,
            'newConferencesCount' => $newConferencesCount,
        ];
    }

    private function pushConf(array $fetchedConferences, $newConferencesCount, $source, $technologie, $conferenceTotal = [])
    {
        $tag = $this->tagRepository->getTagByName($technologie[1]);

        foreach ($fetchedConferences as $fC) {
            $fC = $this->hash($fC);
            $fC->tag = $tag;
            $fC->source = $source;

            $conference = $this->conferenceRepository->findOneBy([
                'hash' => $fC->hash,
            ]);

            if (!$conference) {
                $conference = $this->conferenceRepository->findOneBy([
                    'slug' => $fC->slug, ]);

                // Do not override a conference created by another source
                if ($conference && $conference->getSource() !== $source) {
                    continue;
                }
            }

            if (!$conference) {
                $conference = $this->hydrateConference($fC);

                $this->em->persist($conference);

                array_push($conferenceTotal, $conference);
                ++$newConferencesCount;
            }
        }

        return [
            'conferencesByTag' => $conferenceTotal,
            'newConferenceCount' => $newConferencesCount,
        ];
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

    private function hash(object $fC)
    {
        $fC->name = preg_replace('/ 2\d{3}/', '', $fC->name);

        $fC->slug = Urlizer::transliterate($fC->name);
        $startAt = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $fC->start_date);
        $fC->startAtFormat = $startAt->format('Y-m-d');

        if (isset($fC->end_date)) {
            $fC->endAt = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $fC->end_date);
            $fC->endAtFormat = $fC->endAt->format('Y-m-d');
        } else {
            $fC->endAt = null;
            $fC->endAtFormat = null;
        }

        $fC->hash = hash('md5', $fC->slug.$fC->startAtFormat.$fC->endAtFormat);

        return $fC;
    }
}
