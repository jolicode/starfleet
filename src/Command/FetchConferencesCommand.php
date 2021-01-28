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
use App\Entity\ConferenceFilter;
use App\Fetcher\FetcherInterface;
use App\Repository\ConferenceFilterRepository;
use App\Repository\ConferenceRepository;
use App\Repository\FetcherConfigurationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchConferencesCommand extends Command
{
    /** @var iterable<FetcherInterface> */
    private iterable $fetchers;
    private ObjectManager $em;
    private ConferenceRepository $conferenceRepository;
    private FetcherConfigurationRepository $fetcherConfigurationRepository;
    private ConferenceFilterRepository $conferenceFilterRepository;

    /** @param iterable<FetcherInterface> $fetchers */
    public function __construct(iterable $fetchers, ManagerRegistry $doctrine, ConferenceRepository $conferenceRepository, FetcherConfigurationRepository $fetcherConfigurationRepository, ConferenceFilterRepository $conferenceFilterRepository)
    {
        $this->fetchers = $fetchers;
        $this->em = $doctrine->getManager();
        $this->conferenceRepository = $conferenceRepository;
        $this->fetcherConfigurationRepository = $fetcherConfigurationRepository;
        $this->conferenceFilterRepository = $conferenceFilterRepository;

        parent::__construct();
    }

    protected function configure(): void
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

        $allFilters = $this->conferenceFilterRepository->findAll();

        foreach ($this->fetchers as $fetcher) {
            $io->newLine(1);
            $fetchersSection->write(sprintf('Processing %d/%d fetchers : %s', ++$currentFetcher, $nbFetchers, \get_class($fetcher)));

            $name = (new \ReflectionClass($fetcher))->getShortName();
            $fetcherConfiguration = $this->fetcherConfigurationRepository->findOneOrCreate($name);
            $config = [];

            if (!$fetcherConfiguration->isActive()) {
                continue;
            }

            $config = $fetcherConfiguration->getConfiguration();

            foreach ($fetcher->fetch($config) as $conference) {
                if ($this->shouldBeIgnored($allFilters, $conference)) {
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
        }
        $this->em->flush();

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

        if ($conference->getCity() !== $existingConference->getCity()) {
            $existingConference->setCity($conference->getCity());
            $updated = true;
        }

        if ($conference->getCountry() !== $existingConference->getCountry()) {
            $existingConference->setCountry($conference->getCountry());
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

    /**
     * @param ConferenceFilter[] $filters
     */
    private function shouldBeIgnored(array $filters, Conference $conference): bool
    {
        foreach ($filters as $conferenceFilter) {
            if (fnmatch($conferenceFilter->getName(), $conference->getName(), FNM_CASEFOLD)) {
                $conference->setExcluded(true);

                return true;
            }
        }

        return false;
    }
}
