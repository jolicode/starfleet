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
use App\Fetcher\FetcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class FetchConferencesCommand extends Command
{
    private $fetchers;
    private $em;
    private $serializer;
    private $conferenceRepository;

    public function __construct(iterable $fetchers, RegistryInterface $doctrine, SerializerInterface $serializer)
    {
        $this->fetchers = $fetchers;
        $this->em = $doctrine->getManager();
        $this->serializer = $serializer;
        $this->conferenceRepository = $this->em->getRepository(Conference::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet:conferences:fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conferences = [];

        $newConferencesCount = 0;
        $updatedConferencesCount = 0;

        /** @var FetcherInterface $fetcher */
        foreach ($this->fetchers as $fetcher) {
            $conferences = array_merge($conferences, $fetcher->fetch());
            break;
        }

        foreach ($conferences as $conference) {
            $existingConference = $this->conferenceRepository->findOneBy(['hash' => $conference->getHash()]);

            if ($existingConference instanceof Conference) {
                $this->updateExistingConference($existingConference, $conference);
                ++$updatedConferencesCount;
            } else {
                ++$newConferencesCount;
                $this->em->persist($conference);
            }
        }

        $this->em->flush();

        $output->writeln($newConferencesCount.' newly added conference(s)');
        $output->writeln($updatedConferencesCount.' updated conference(s)');
    }

    protected function updateExistingConference(Conference $existingConference, Conference $conference)
    {
        $existingConference->setDescription($conference->getDescription() ?? $existingConference->getDescription());
        $existingConference->setLocation($conference->getLocation() ?? $existingConference->getLocation());
        $existingConference->setStartAt($conference->getStartAt() ?? $existingConference->getStartAt());
        $existingConference->setEndAt($conference->getEndAt() ?? $existingConference->getEndAt());
        $existingConference->setCfpUrl($conference->getCfpUrl() ?? $existingConference->getCfpUrl());
        $existingConference->setCfpEndAt($conference->getCfpEndAt() ?? $existingConference->getCfpEndAt());
        $existingConference->setSiteUrl($conference->getSiteUrl() ?? $existingConference->getSiteUrl());
    }
}
