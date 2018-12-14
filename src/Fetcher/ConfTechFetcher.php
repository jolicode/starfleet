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

        $conferencesFinal = [];
        $newConferencesCount = 0;
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
                $conferenceTotal = [];

                $conferenceTotal = $this->pushConf($fetchedConferences, $newConferencesCount, $source, $technologie, $conferenceTotal);

                $newConferencesCount = $conferenceTotal['newConferenceCount'];
                array_push($conferencesFinal, $conferenceTotal['conferenceTotal']);
            }
        }
        array_push($conferencesFinal, $newConferencesCount);

        return $conferencesFinal;
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }

    private function pushConf(array $fetchedConferences, $newConferencesCount, $source, $technologie, $conferenceTotal = [])
    {
        $tag = $this->tagRepository->getTagByName($technologie[1]);

        foreach ($fetchedConferences as $fC) {
            $fC = $this->hash($fC);
            $fC->tag = $tag;
            $fC->source = $source;

            $conference = $this->repository->findOneBy([
            'hash' => $fC->hash,
            ]);

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

                array_push($conferenceTotal, $conference);
                ++$newConferencesCount;
            }
        }

        return [
            'conferenceTotal' => $conferenceTotal,
            'newConferenceCount' => $newConferencesCount,
        ];
    }

    private function matchTags()
    {
        $dateCurrentYear = date('Y');
        $dateNextYear = date('Y', strtotime('+1 year'));

        $tagsSelected = $this->tagRepository->getTagsBySelected();

        $confTechFetcherSynonyms = [
            0 => 'android',
            1 => null,
            2 => null,
            3 => 'css',
            4 => null,
            5 => 'data',
            6 => 'devops',
            7 => 'dotnet',
            8 => 'elixir',
            9 => null,
            10 => null,
            11 => 'general',
            12 => 'golang',
            13 => null,
            14 => 'graphql',
            15 => null,
            16 => 'ios',
            17 => null,
            18 => 'javascript',
            19 => null,
            20 => null,
            21 => 'php',
            22 => 'python',
            23 => null,
            24 => 'ruby',
            25 => 'rust',
            26 => null,
            27 => 'security',
            28 => 'tech-comm',
            29 => 'ux',
        ];

        $tagMatch = array_combine(TagEnum::toArray(), $confTechFetcherSynonyms);

        $params = [
            $dateCurrentYear => [],
            $dateNextYear => [],
        ];

        foreach ($tagsSelected as $tag) {
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
            $cfpEndAt = \DateTime::createFromFormat('Y-m-d', $fC->cfpEndDate);
            $conference->setCfpEndAt($cfpEndAt);
        }

        return $conference;
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
