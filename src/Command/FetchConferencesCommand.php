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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
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

    /**
     * @param ConsoleOutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newConferencesCount = 0;
        $updatedConferencesCount = 0;
        $notMatchedConferences = [];

        $nbFetchers = \count($this->fetchers);
        $currentFetcher = 0;
        $fetchersSection = $output->section();
        $fetchersSection->write(sprintf('Processing %d/%d fetchers : %s', $currentFetcher, $nbFetchers, ''), true);

        /** @var FetcherInterface $fetcher */
        foreach ($this->fetchers as $fetcher) {
            $fetchersSection->overwrite(sprintf('Processing %d/%d fetchers : %s', ++$currentFetcher, $nbFetchers, \get_class($fetcher)), true);

            if (!$fetcher->isActive()) {
                continue;
            }

            foreach ($fetcher->fetch() as $url => $conferences) {
                foreach ($conferences as $conference) {
                    if ($conference->getExcluded()) {
                        continue;
                    }

                    $matchedConference = $this->retrieveConferenceIfExists($conference);

                    if ($matchedConference instanceof Conference) {
                        if ($this->updateExistingConference($matchedConference, $conference)) {
                            ++$updatedConferencesCount;
                        }
                    } else {
                        $notMatchedConferences[] = $conference;
                        $this->em->persist($conference);
                        ++$newConferencesCount;
                    }
                }

                $this->em->flush();
            }
        }

        $io->newLine(1);
        $io->success($newConferencesCount.' newly added conference(s)');
        $io->success($updatedConferencesCount.' updated conference(s)');

        return 0;
    }

    protected function retrieveConferenceIfExists(Conference $conference): ?Conference
    {
        $currentConferenceName = preg_replace('/(20)[0-9][0-9]/', '', $conference->getName());

        $matchedConference = null;
        $existingConferences = $this->conferenceRepository->getAllConferencesAsRow();

        $shortest = -1;

        foreach ($existingConferences as $existingConference) {
            $existingConferenceName = preg_replace('/(20)[0-9][0-9]/', '', $existingConference['name']);

            $distance = levenshtein(strtolower($currentConferenceName), strtolower($existingConferenceName));

            if (
                0 === $distance
                && $existingConference['startAt']->format('Y-m-d') === $conference->getStartAt()->format('Y-m-d')
                && $existingConference['endAt']->format('Y-m-d') === $conference->getEndAt()->format('Y-m-d')
            ) {
                $matchedConference = $conference;
                $shortest = $distance;
                break;
            }

            if ($distance <= $shortest || $shortest < 0) {
                if (
                    $existingConference['startAt']->format('Y-m-d') === $conference->getStartAt()->format('Y-m-d')
                    && $existingConference['endAt']->format('Y-m-d') === $conference->getEndAt()->format('Y-m-d')
                ) {
                    $matchedConference = $conference;
                }
                $shortest = $distance;
            }
        }

        if ($shortest > 3) {
            $matchedConference = null;
        }

        return $matchedConference;
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
