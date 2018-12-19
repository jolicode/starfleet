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
use App\Fetcher\ConfTechFetcher;
use App\Fetcher\FetcherInterface;
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
    private $messageFactory;
    private $client;
    private $fetcher;
    private $appFetchers;
    private $conferenceRepository;

    public function __construct(iterable $appFetchers, RegistryInterface $doctrine, MessageFactory $messageFactory, HttpClient $client, ConfTechFetcher $fetcher)
    {
        $this->em = $doctrine->getManager();
        $this->messageFactory = $messageFactory;
        $this->client = $client;
        $this->fetcher = $fetcher;
        $this->appFetchers = $appFetchers;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet-conferences-fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conferences = [];

        $fetchers = $this->appFetchers;
//        $fetcher = $this->fetcher;

        $newConferencesCount = 0;
        $updateConferencesCount = 0;

        foreach ($fetchers as $fetcher) {
            /** @var FetcherInterface $fetcher */
            $conferencesBySource = $fetcher->fetch();
            $conferences = array_merge($conferences, $conferencesBySource);
        }

        foreach ($conferences as $conference) {
            $statut = $this->conferencesDo($conference);
            if ('new' === $statut['statut']) {
                ++$newConferencesCount;
                $this->em->persist($conference);
            } elseif ('updated' === $statut['statut']) {
                ++$updateConferencesCount;
                $this->em->persist($statut['conference']);
            } else {
                continue;
            }
        }

        $this->em->flush();

        $output->writeln('You add '.($newConferencesCount).' conference(s)');
        $output->writeln('You update '.($updateConferencesCount).' conference(s)');
    }

    protected function conferencesDo(Conference $conference)
    {
        /** @var Conference $existedConference */
        $existedConference = $this->conferenceRepository->findOneByHash($conference->getHash());

        if ($existedConference) {
            $isConferenceUpdated = $this->hydrateConferenceUpdate($existedConference, $conference);
            if ($isConferenceUpdated) {
                return [
                    'statut' => 'updated',
                    'conference' => $existedConference,
                ];
            }

            return [
                'statut' => 'old',
            ];
        }

        if (!$existedConference) {
            $existedConference = $this->conferenceRepository->findOneBy([
                'slug' => $conference->getSlug(), ]);

            // Do not override a conference created by another source
            if ($existedConference && $existedConference->getSource() !== $conference->getSource()) {
                return [
                    'statut' => 'old',
                ];
            }
        }

        return [
            'statut' => 'new',
        ];
    }

    private function hydrateConferenceUpdate(Conference $eC, Conference $c)
    {
        $modified = false;

        if ($eC->getCfpUrl() !== $c->getCfpUrl()) {
            $eC->setCfpUrl($c->getCfpUrl());
            $modified = true;
        }

        if ($eC->getCfpEndAt() !== $c->getCfpEndAt()) {
            $eC->setCfpEndAt($c->getCfpEndAt());
            $modified = true;
        }

        if ($eC->getDescription() !== $c->getDescription()) {
            $eC->setDescription($c->getDescription());
            $modified = true;
        }

        if ($eC->getSiteUrl() !== $c->getSiteUrl()) {
            $eC->setSiteUrl($c->getSiteUrl());
            $modified = true;
        }

        return $modified;
    }
}
