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

class ConfTechFetcher implements FetcherInterface
{
    const SOURCE = 'conf-tech';
    private $em;
    private $repository;
    private $logger;
    private $tagRepository;

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->repository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
    }

    public function getUrl(array $params = []): string
    {
        return "https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/$params[date]/$params[tag].json";
    }

    public function fetch(): array
    {
        $params = $this->matchTags();

        $allNewConferences = [];
        $newConferencesCount = 0;
        $updateConferencesCount = 0;
        $client = new Client();

        foreach ($params as $date => $technologies) {
            foreach ($technologies as $technologie) {
                try {
                    $response = $client->request('GET', $this->getUrl(['date' => $date, 'tag' => $technologie[0]]));
                    $fetchedConferences = json_decode($response->getBody());
                } catch (GuzzleException $e) {
                    if (404 === $e->getCode()) {
                        $this->logger->error($e->getMessage());
                        $fetchedConferences = [];
                    } else {
                        $this->logger->error($e->getMessage());
                        throw new Exception($e);
                    }
                }

                $source = self::SOURCE;
                $conferencesByTag = [];

                $conferencesByTag = $this->pushConf($fetchedConferences, $newConferencesCount, $updateConferencesCount, $source, $technologie, $conferencesByTag);

                $newConferencesCount = $conferencesByTag['newConferencesCount'];
                $updateConferencesCount = $conferencesByTag['updateConferencesCount'];
                if (array_key_exists($technologie[1], $allNewConferences)) {
                    $allNewConferences[$technologie[1]] = array_merge($allNewConferences[$technologie[1]], $conferencesByTag['conferencesByTag']);
                } else {
                    $allNewConferences[$technologie[1]] = $conferencesByTag['conferencesByTag'];
                }
            }
        }

        return [
            'conferences' => $allNewConferences,
            'newConferencesCount' => $newConferencesCount,
            'updateConferencesCount' => $updateConferencesCount,
        ];
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }

    private function pushConf(array $fetchedConferences, $newConferencesCount, $updateConferencesCount, $source, $technologie, $conferencesByTag = [])
    {
        $tag = $this->tagRepository->getTagByName($technologie[1]);

        foreach ($fetchedConferences as $fC) {
            $fC = $this->hash($fC);
            $fC->tag = $tag;
            $fC->source = $source;

            $conference = $this->repository->findOneBy([
            'hash' => $fC->hash,
            ]);

            if ($conference) {
                $isConferenceUpdated = $this->hydrateConferenceUpdate($conference, $fC);
                if ($isConferenceUpdated) {
                    ++$updateConferencesCount;
                }
            }

            if (!$conference) {
                $conference = $this->repository->findOneBy([
                    'slug' => $fC->slug, ]);

                // Do not override a conference created by another source
                if ($conference && $conference->getSource() !== $source) {
                    continue;
                }
            }

            if (!$conference) {
                $conference = $this->hydrateConference($fC);

                $this->em->persist($conference);

                array_push($conferencesByTag, $conference);
                ++$newConferencesCount;
            }
        }

        return [
            'conferencesByTag' => $conferencesByTag,
            'newConferencesCount' => $newConferencesCount,
            'updateConferencesCount' => $updateConferencesCount,
        ];
    }

    private function matchTags()
    {
        $dateCurrentYear = date('Y');
        $dateNextYear = date('Y', strtotime('+1 year'));

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

        $tagMatch = array_combine(TagEnum::toArray(), $confTechFetcherSynonyms);

        $params = [
        $dateCurrentYear => [],
        $dateNextYear => [],
    ];

        foreach ($tagsSelected as $tag) {
            /* @var Tag $tag*/
            if (null !== $tagMatch[$tag->getName()]) {
                array_push($params[$dateCurrentYear], [$tagMatch[$tag->getName()], $tag->getName()]);
                array_push($params[$dateNextYear], [$tagMatch[$tag->getName()], $tag->getName()]);
            }
        }

        return $params;
    }

    private function hydrateConference($fC)
    {
        $conference = new Conference();
        $conference->setSource($fC->source);
        $conference->setHash($fC->hash);
        $conference->setSlug($fC->slug);
        $conference->setName($fC->name);
        $conference->setLocation($this->getLocation($fC));
        $conference->setStartAt(\DateTime::createFromFormat('Y-m-d', $fC->startDate));
        $conference->setEndAt($fC->endAt);
        $conference->setSiteUrl($fC->url);
        $conference->addTag($fC->tag);

        if (isset($fC->description)) {
            $conference->setDescription($fC->description);
        }

        if (isset($fC->cfpUrl)) {
            $conference->setCfpUrl($fC->cfpUrl);
        }

        if (isset($fC->cfpEndDate)) {
            $cfpEndAt = \DateTime::createFromFormat('Y-m-d h:i:s', $fC->cfpEndDate.' 00:00:00');
            $conference->setCfpEndAt($cfpEndAt);
        }

        return $conference;
    }

    private function hydrateConferenceUpdate(Conference $conference, $fC)
    {
        $c = $conference;
        $modified = false;

        if (isset($fC->cfpUrl)) {
            if ($c->getCfpUrl() !== $fC->cfpUrl) {
                $c->setCfpUrl($fC->cfpUrl);
                $modified = true;
            }
        }

        if (isset($fC->cfpEndDate)) {
            if ($c->getCfpEndAt() !== \DateTime::createFromFormat('Y-m-d h:i:s', $fC->cfpEndDate.' 00:00:00')) {
                $cfpEndAt = \DateTime::createFromFormat('Y-m-d h:i:s', $fC->cfpEndDate.' 00:00:00');
                $conference->setCfpEndAt($cfpEndAt);
                $modified = true;
            }
        }

        if (isset($fC->description)) {
            if ($c->getDescription() !== $fC->description) {
                $conference->setDescription($fC->description);
                $modified = true;
            }
        }

        if ($c->getSiteUrl() !== $fC->url) {
            $conference->setSiteUrl($fC->url);
            $modified = true;
        }

        return $modified;
    }

    private function hash(object $fC)
    {
        $fC->name = preg_replace('/ 2\d{3}/', '', $fC->name);

        $fC->slug = Urlizer::transliterate($fC->name);
        $startAt = \DateTime::createFromFormat('Y-m-d', $fC->startDate);
        $fC->startAtFormat = $startAt->format('Y-m-d');

        if (isset($fC->endDate)) {
            $fC->endAt = \DateTime::createFromFormat('Y-m-d', $fC->endDate);
            $fC->endAtFormat = $fC->endAt->format('Y-m-d');
        } else {
            $fC->endAt = null;
            $fC->endAtFormat = null;
        }

        $fC->hash = hash('md5', $fC->slug.$fC->startAtFormat.$fC->endAtFormat);

        return $fC;
    }
}
