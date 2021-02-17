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
use App\Entity\Continent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyFetcher implements FetcherInterface
{
    private const SOURCE = 'Symfony';
    private const SYMFONY_SOURCE_URL = 'https://live.symfony.com/api/conference/all.json';

    private LocationGuesser $locationGuesser;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(LocationGuesser $locationGuesser, ?HttpClientInterface $client = null, ?LoggerInterface $logger = null)
    {
        $this->locationGuesser = $locationGuesser;
        $this->client = $client ?: HttpClient::create();
        $this->logger = $logger ?: new NullLogger();
    }

    public function fetch(array $configuration = []): \Generator
    {
        foreach ($this->querySymfony() as $conference) {
            if (!\array_key_exists('ends_at', $conference) || !$conference['ends_at']['date']) {
                continue;
            }

            $endDate = new \DateTime($conference['ends_at']['date']);

            if (new \DateTime('now') < $endDate) {
                yield $this->denormalizeConference($conference);
            }
        }
    }

    public function configureForm(FormBuilderInterface $formBuilder): void
    {
    }

    /** @param array<mixed> $rawConference */
    private function denormalizeConference(array $rawConference): ?Conference
    {
        if (!$rawConference['is_online']) {
            $city = $rawConference['city'];
            $continent = $this->locationGuesser->getContinent($city);

            if (!$continent instanceof Continent || !$continent->getEnabled()) {
                return null;
            }
        }

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($rawConference['slug']);
        $conference->setName($rawConference['name']);
        $conference->setSiteUrl($rawConference['home_url']);
        $conference->addTag('Symfony');
        $conference->addTag('PHP');

        if (\array_key_exists('starts_at', $rawConference)) {
            $startDate = new \DateTimeImmutable($rawConference['starts_at']['date']);
            $conference->setStartAt($startDate);
        }

        if (\array_key_exists('ends_at', $rawConference) && $rawConference['ends_at']) {
            $endDate = new \DateTimeImmutable($rawConference['ends_at']['date']);
            $conference->setEndAt($endDate);
        }

        if (\array_key_exists('cfp_starts_at', $rawConference) && $rawConference['cfp_starts_at']) {
            $cfpStartAt = new \DateTimeImmutable($rawConference['cfp_starts_at']['date']);
            $conference->setCfpEndAt($cfpStartAt);
        }

        if (\array_key_exists('cfp_ends_at', $rawConference) && $rawConference['cfp_ends_at']) {
            $cfpEndAt = new \DateTimeImmutable($rawConference['cfp_ends_at']['date']);
            $conference->setCfpEndAt($cfpEndAt);
        }

        if ($rawConference['is_online']) {
            $conference->setCity('Online');
            $conference->setOnline(true);
        } else {
            $conference->setCity($rawConference['city']);
            $conference->setCountry($rawConference['country']);
        }

        return $conference;
    }

    /** @return array<mixed>|null */
    private function querySymfony(): ?array
    {
        try {
            $response = $this->client->request('GET', self::SYMFONY_SOURCE_URL);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('HttpClient Transport Exception', [
                'url' => self::SYMFONY_SOURCE_URL,
                'source' => self::SOURCE,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }

        if (200 !== $statusCode = $response->getStatusCode()) {
            $this->logger->error(sprintf('Source URL returns %d', $statusCode), [
                'url' => self::SYMFONY_SOURCE_URL,
                'source' => self::SOURCE,
            ]);

            return null;
        }

        $result = $response->toArray();

        return $result;
    }
}