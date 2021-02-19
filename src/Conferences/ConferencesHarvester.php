<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Conferences;

use App\Entity\Conference;
use App\Entity\ConferenceFilter;
use App\Fetcher\FetcherInterface;
use App\Repository\ConferenceFilterRepository;
use App\Repository\ConferenceRepository;
use App\Repository\FetcherConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ConferencesHarvester
{
    private FetcherConfigurationRepository $fetcherConfigurationRepository;
    private ConferenceFilterRepository $conferenceFilterRepository;
    private ConferenceRepository $conferenceRepository;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    /** @var iterable<FetcherInterface> */
    private iterable $fetchers;
    /** @var array<ConferenceFilter> */
    private array $conferenceFilters;

    /** @param iterable<FetcherInterface> $fetchers */
    public function __construct(iterable $fetchers, FetcherConfigurationRepository $fetcherConfigurationRepository, ConferenceFilterRepository $conferenceFilterRepository, ConferenceRepository $conferenceRepository, EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->fetchers = $fetchers;
        $this->fetcherConfigurationRepository = $fetcherConfigurationRepository;
        $this->conferenceFilterRepository = $conferenceFilterRepository;
        $this->conferenceRepository = $conferenceRepository;
        $this->em = $em;
        $this->logger = $logger ?: new NullLogger();
    }

    public function harvest(): void
    {
        $fetchersAmount = \count($this->fetchers);
        $currentFetcher = 0;
        $updatedConferencesCount = 0;
        $newConferencesCount = 0;

        foreach ($this->fetchers as $fetcher) {
            $this->logger->info(sprintf('Processing %d/%d fetchers : %s', ++$currentFetcher, $fetchersAmount, \get_class($fetcher)));

            $name = (new \ReflectionClass($fetcher))->getShortName();
            $fetcherConfiguration = $this->fetcherConfigurationRepository->findOneOrCreate($name);

            if (!$fetcherConfiguration->isActive()) {
                return;
            }

            $config = $fetcherConfiguration->getConfiguration();

            foreach ($fetcher->fetch($config) as $conference) {
                if ($this->shouldBeIgnored($conference)) {
                    continue;
                }

                $matchedConference = $this->conferenceRepository->findExistingConference($conference);

                if ($matchedConference instanceof Conference) {
                    if ($this->updateExistingConference($matchedConference, $conference)) {
                        ++$updatedConferencesCount;
                    }
                } else {
                    $this->em->persist($conference);
                    ++$newConferencesCount;
                }
            }

            $this->em->flush();
        }

        $this->logger->notice($newConferencesCount.' newly added conference(s)');
        $this->logger->notice($updatedConferencesCount.' updated conference(s)');
    }

    private function updateExistingConference(Conference $existingConference, Conference $conference): bool
    {
        $updated = false;

        if ($conference->getName() !== $existingConference->getName()) {
            $existingConference->setName($conference->getName());
            $updated = true;
        }

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

        if ($conference->isOnline() !== $existingConference->isOnline()) {
            $existingConference->setOnline($conference->isOnline());
            $updated = true;
        }

        return $updated;
    }

    private function shouldBeIgnored(Conference $conference): bool
    {
        foreach ($this->getFilters() as $conferenceFilter) {
            $filterName = $conferenceFilter->getName();
            if (fnmatch($filterName, $conference->getName(), FNM_CASEFOLD) || \in_array($filterName, $conference->getTags())) {
                $conference->setExcluded(true);

                return true;
            }
        }

        return false;
    }

    /** @return array<ConferenceFilter> */
    private function getFilters(): array
    {
        return $this->conferenceFilters ??= $this->conferenceFilterRepository->findAll();
    }
}
