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
    private $conferenceRepository;
    private $logger;
    private $tagRepository;

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        $this->tagRepository = $this->em->getRepository(Tag::class);
    }

    public function getUrl(array $params = []): string
    {
        return "https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/$params[date]/$params[tag].json";
    }

    public function fetch(): array
    {
        $params = $this->matchTags();

        $conferences = [];
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
                $conferences = $this->confToArray($fetchedConferences, $source, $technologie, $conferences);
            }
        }

        return $conferences;
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }

    private function confToArray(array $fetchedConferences, $source, $technologie, $conferences = [])
    {
        $tag = $this->tagRepository->getTagByName($technologie[1]);

        foreach ($fetchedConferences as $fC) {
            $fC = $this->hash($fC);
            $fC->tag = $tag;
            $fC->source = $source;

            $conference = $this->hydrateConference($fC);

            $conferences[$conference->getHash()] = $conference;
        }

        return $conferences;
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
        $conference->setStartAt(\DateTime::createFromFormat('Y-m-d h:i:s', $fC->startDate.' 00:00:00'));
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

    private function hash(object $fC)
    {
        // Remove year so every conference is year empty
        $fC->name = preg_replace('/ 2\d{3}/', '', $fC->name);
        $startAt = \DateTime::createFromFormat('Y-m-d h:i:s', $fC->startDate.' 00:00:00');
        $conferenceYearDate = $startAt->format('Y');

        $fC->slug = Urlizer::transliterate($fC->name." $fC->city"." $conferenceYearDate");
        $fC->name = $fC->name." $conferenceYearDate";
        $fC->startAtFormat = $startAt->format('Y-m-d');

        if (isset($fC->endDate)) {
            $fC->endAt = \DateTime::createFromFormat('Y-m-d h:i:s', $fC->endDate.' 00:00:00');
            $fC->endAtFormat = $fC->endAt->format('Y-m-d');
        } else {
            $fC->endAt = null;
            $fC->endAtFormat = null;
        }

        $fC->hash = hash('md5', $fC->slug.$fC->startAtFormat);

        return $fC;
    }
}
