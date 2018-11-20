<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Conference;
use App\Entity\Tag;
use App\Fetcher\ConfTechFetcher;
use Gedmo\Sluggable\Util\Urlizer;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchConferencesCommand extends Command
{
    const SALOON_URL = 'http://saloonapp.herokuapp.com/api/v1/conferences?tags=';

    private $em;
    private $repository;
    private $messageFactory;
    private $client;
    private $fetcher;

    public function __construct(RegistryInterface $doctrine, MessageFactory $messageFactory, HttpClient $client, ConfTechFetcher $fetcher)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $this->em->getRepository(Conference::class);

        $this->messageFactory = $messageFactory;
        $this->client = $client;
        $this->fetcher = $fetcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet-conferences-fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = $this->em->getRepository(Tag::class)->findAll();
        $tagsList = implode(',', array_map(function ($tag) { return $tag->getName(); }, $tags));

        $source = Conference::SOURCE_CONFS_TECH;
        $newConferencesCount = 0;

        $response = $this->fetcher->fetch();
//        $response = $this->client->sendRequest($this->messageFactory->createRequest('GET', self::SALOON_URL.$tagsList));

//        $fetchedConferences = (array) json_decode($response->getBody()->getContents())->result;
        $fetchedConferences = (array) json_decode($response->getBody());

        $i = 100;

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
            $conference->setRemoteId($i);
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

        $this->em->flush();
    }
}
