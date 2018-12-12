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

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->repository = $this->em->getRepository(Conference::class);
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

        foreach ($params as $date => $technologies) {
            foreach ($technologies as $technologie) {
                $client = new Client();
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
        $tag = $this->getTagByName($technologie[1]);

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
                $conference = $this->setParams($fC);

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

        $tagsSelected = $this->em->getRepository(Tag::class)->findBy(['selected' => true]);

        $tagMatch = [
            'Android' => 'android',
            'Apple' => null,
            'CSS' => 'css',
            'C++' => null,
            'Dart' => null,
            'Data' => 'data',
            'DevOps' => 'devops',
            'Dotnet' => 'dotnet',
            'Elixir' => 'elixir',
            'Facebook' => null,
            'Flutter' => null,
            'General' => 'general',
            'Go' => 'golang',
            'Google' => null,
            'GraphQL' => 'graphql',
            'HTML' => null,
            'iOS' => 'ios',
            'Java' => null,
            'Javascript' => 'javascript',
            'Microsoft' => null,
            'NodeJS' => null,
            'PHP' => 'php',
            'Python' => 'python',
            'React Native' => null,
            'Ruby' => 'ruby',
            'Rust' => 'rust',
            'Scala' => null,
            'Security' => 'security',
            'TechComm' => 'tech-comm',
            'UX' => 'ux',
        ];

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

    private function getTagByName(string $tagName): Tag
    {
        $tag = $this->em->getRepository(Tag::class)->findOneBy([
            'name' => $tagName,
        ]);

        return $tag;
    }

    private function setParams($fC)
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
