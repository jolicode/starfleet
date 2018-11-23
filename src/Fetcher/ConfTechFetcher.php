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

use Gedmo\Sluggable\Util\Urlizer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ConfTechFetcher implements FetcherInterface
{
    const SOURCE = 'conf-tech';

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getUrl(): string
    {
        return 'https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/__date__/__tag__.json';
    }

    public function fetch(): array
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $this->getUrl());
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
        }

        $fetchedConferences = json_decode($response->getBody());

        foreach ($fetchedConferences as $fetchedConference) {
            $slug = Urlizer::transliterate($fetchedConference->name);
            $startAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate);
            $startAtFormat = $startAt->format('Y-m-d');
            $endAt = \DateTime::createFromFormat('Y-m-d', $fetchedConference->endDate);
            $endAtFormat = $endAt->format('Y-m-d');
            $hash = hash('sha1', $slug.$startAtFormat.$endAtFormat);

            $conference = $this->repository->findOneBy([
                'hash' => $hash,
            ]);

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
            $conference->setLocation($this->fetcher->getLocation($fetchedConference));
            $conference->setStartAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->startDate));
            $conference->setEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->endDate));
            $conference->setSiteUrl($fetchedConference->url);

            if (isset($fetchedConference->description)) {
                $conference->setDescription($fetchedConference->description);
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
                $conference->setCfpUrl($fetchedConference->cfpUrl);
                $conference->setCfpEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->cfpEndDate));
            }
            ++$i;
        }

        return $response;
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }
}
