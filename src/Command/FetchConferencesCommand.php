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
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

class FetchConferencesCommand extends Command
{
    private $fetchers;
    private $em;
    private $serializer;
    private $conferenceRepository;

    public function __construct(iterable $fetchers, ManagerRegistry $doctrine, SerializerInterface $serializer)
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
        $symfonyStyle = new SymfonyStyle($input, $output);

        $newConferences = [];
        $newConferencesCount = 0;
        $updatedConferencesCount = 0;

        /** @var FetcherInterface $fetcher */
        foreach ($this->fetchers as $fetcher) {
            if (!$fetcher->isActive()) {
                continue;
            }

            $symfonyStyle->title(\get_class($fetcher).' is running...');

            $conferences = $fetcher->fetch();

            $progressBar = $symfonyStyle->createProgressBar(\count($conferences));

            foreach ($conferences as $conference) {
                $existingConference = $this->conferenceRepository->findOneBy(['hash' => $conference->getHash()]);

                if ($existingConference instanceof Conference) {
                    if ($this->updateExistingConference($existingConference, $conference)) {
                        ++$updatedConferencesCount;
                    }
                } else {
                    if ($conference->getExcluded()) {
                        continue;
                    }
                    $newConferences[] = $conference;
                    ++$newConferencesCount;
                    $this->em->persist($conference);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $symfonyStyle->write("\n\n");
            unset($progressBar);

            $this->em->flush();
        }

        $symfonyStyle->writeln("\n");
        $symfonyStyle->success($newConferencesCount.' newly added conference(s)');
        $symfonyStyle->success($updatedConferencesCount.' updated conference(s)');
    }

    protected function updateExistingConference(Conference $existingConference, Conference $conference): bool
    {
        $updated = false;

        if ($conference->getDescription() !== $existingConference->getDescription()) {
            $existingConference->setDescription($conference->getDescription());
            $updated = true;
        }

        if ($conference->getLocation() !== $existingConference->getLocation()) {
            $existingConference->setLocation($conference->getLocation());
            $updated = true;
        }

        if ($conference->getStartAt() instanceof \DateTimeInterface && $existingConference->getStartAt() instanceof \DateTimeInterface) {
            if ($conference->getStartAt()->format(\DateTime::ISO8601) !== $existingConference->getStartAt()->format(\DateTime::ISO8601)) {
                $existingConference->setStartAt($conference->getStartAt());
                $updated = true;
            }
        }

        if ($conference->getEndAt() instanceof \DateTimeInterface && $existingConference->getEndAt() instanceof \DateTimeInterface) {
            if ($conference->getEndAt()->format(\DateTime::ISO8601) !== $existingConference->getEndAt()->format(\DateTime::ISO8601)) {
                $existingConference->setEndAt($conference->getEndAt());
                $updated = true;
            }
        }

        if ($conference->getCfpUrl() !== $existingConference->getCfpUrl()) {
            $existingConference->setCfpUrl($conference->getCfpUrl());
            $updated = true;
        }

        if ($conference->getCfpEndAt() instanceof \DateTimeInterface && $existingConference->getCfpEndAt() instanceof \DateTimeInterface) {
            if ($conference->getCfpEndAt()->format(\DateTime::ISO8601) !== $existingConference->getCfpEndAt()->format(\DateTime::ISO8601)) {
                $existingConference->setCfpEndAt($conference->getCfpEndAt());
                $updated = true;
            }
        }

        if ($conference->getSiteUrl() !== $existingConference->getSiteUrl()) {
            $existingConference->setSiteUrl($conference->getSiteUrl());
            $updated = true;
        }

        if ($conference->getExcluded() !== $existingConference->getExcluded()) {
            $existingConference->setExcluded($conference->getExcluded());
            $updated = true;
        }

        return $updated;
    }
}
