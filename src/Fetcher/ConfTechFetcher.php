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
use Gedmo\Sluggable\Util\Urlizer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

    public function getUrl(array $params): string
    {
        return "https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/$params[date]/$params[tag].json";
    }

    public function fetch(): array
    {
        $params = [
            '2018' => [
                'php',
                'javascript',
                'golang',
            ],
            '2019' => [
                'php',
                'ruby',
                'css',
            ],
        ];

        $conferencesFinal = [];

        foreach ($params as $date => $technologies) {
            foreach ($technologies as $technologie) {
                $client = new Client();
                try {
                    $response = $client->request('GET', $this->getUrl(['date' => $date, 'tag' => $technologie]));
                } catch (GuzzleException $e) {
                    $this->logger->error($e->getMessage());
                }

                $fetchedConferences = json_decode($response->getBody());

                $newConferencesCount = 0;
                $source = self::SOURCE;

                $conferenceTotal = [];

                foreach ($fetchedConferences as $fetchedConference) {
                    $slug = Urlizer::transliterate($fetchedConference->name);
                    $startAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate);
                    $startAtFormat = $startAt->format('Y-m-d');
//                    $endAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->endDate);
                    $endAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate);
                    $endAtFormat = $endAt->format('Y-m-d');
                    $hash = hash('md5', $slug.$startAtFormat.$endAtFormat);

                    $conference = $this->repository->findOneBy([
                        'hash' => $hash,
                    ]);

                    $conference1['source'] = $source;
                    $conference1['hash'] = $hash;
                    $conference1['slug'] = $slug;
                    $conference1['name'] = $fetchedConference->name;
                    $conference1['location'] = $this->getLocation($fetchedConference);
                    $conference1['start_at'] = $startAt;
                    $conference1['end_at'] = $endAt;

                    if (!$conference) {
                        $conference = $this->repository->findOneBySlug($slug);

                        // Do not override a conference created by another source
                        if ($conference && $conference->getSource() !== $source) {
                            continue;
                        }
                    }

                    if (!$conference) {
                        $conference = new Conference();
                        $this->em->persist($conference);
                        ++$newConferencesCount;
                    }

                    $conference->setSource($source);
                    $conference->setHash($hash);
                    $conference->setSlug($slug);
                    $conference->setName($fetchedConference->name);
                    $conference->setLocation($this->getLocation($fetchedConference));
                    $conference->setStartAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate));
//                    $conference->setEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->endDate));
                    $conference->setEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate));
                    $conference->setSiteUrl($fetchedConference->url);

                    if (isset($fetchedConference->description)) {
                        $conference->setDescription($fetchedConference->description);
                        $conference1['description'] = $fetchedConference->description;
                    }

                    if (isset($fetchedConference->tags)) {
                        foreach ($fetchedConference->tags as $fetchedTag) {
                            $tag = $this->em->getRepository(Tag::class)->findOneBy([
                                'name' => $fetchedTag,
                            ]);

                            if ($tag instanceof Tag) {
                                $conference->addTag($tag);
                            }
                        }
                    }

                    if (isset($fetchedConference->cfpUrl, $fetchedConference->cfpEndDate)) {
                        $cfpEndAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->cfpEndDate);

                        $conference->setCfpUrl($fetchedConference->cfpUrl);
                        $conference->setCfpEndAt($cfpEndAt);
                        $conference1['cfp_url'] = $fetchedConference->cfpUrl;
                        $conference1['cfp_end_at'] = $cfpEndAt;
                    } else {
                        $conference1['cfp_url'] = null;
                        $conference1['cfp_end_at'] = null;
                    }

                    array_push($conferenceTotal, $conference1);

                    ++$newConferencesCount;
                    $conference1 = [];
                }

                array_push($conferencesFinal, $conferenceTotal);
            }
        }

        return $conferencesFinal;
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }
}
