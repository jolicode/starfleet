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

    public function __construct(RegistryInterface $doctrine, MessageFactory $messageFactory, HttpClient $client)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $this->em->getRepository(Conference::class);

        $this->messageFactory = $messageFactory;
        $this->client = $client;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet-conferences-fetch');
        $this->setDescription('Fetch conferences from http://saloonapp.herokuapp.com/conferences');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = $this->em->getRepository(Tag::class)->findAll();
        $tagsList = implode(',', array_map(function ($tag) { return $tag->getName(); }, $tags));

        $source = Conference::SOURCE_SALOON;
        $newConferencesCount = 0;

        $response = $this->client->sendRequest($this->messageFactory->createRequest('GET', self::SALOON_URL.$tagsList));

        $fetchedConferences = (array) json_decode($response->getBody()->getContents())->result;

        foreach ($fetchedConferences as $fetchedConference) {
            $slug = Urlizer::transliterate($fetchedConference->name);

            $remoteId = $fetchedConference->id;

            $conference = $this->repository->findOneBy([
                'remoteId' => $remoteId,
                'source' => $source,
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
            $conference->setRemoteId($remoteId);
            $conference->setSlug($slug);
            $conference->setName($fetchedConference->name);
            $conference->setLocation($fetchedConference->location->locality.', '.$fetchedConference->location->country);
            $conference->setStartAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->start));
            $conference->setEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->end));
            $conference->setSiteUrl($fetchedConference->siteUrl);

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

            if (isset($fetchedConference->cfp)) {
                $conference->setCfpUrl($fetchedConference->cfp->siteUrl);
                $conference->setCfpEndAt(\DateTime::createFromFormat('Y-m-d', $fetchedConference->cfp->end));
            }
        }

        $this->em->flush();
    }
}
