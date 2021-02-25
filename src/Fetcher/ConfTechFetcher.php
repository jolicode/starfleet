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
use Behat\Transliterator\Transliterator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfTechFetcher implements FetcherInterface
{
    private const SOURCE = 'conf-tech';

    // These are all the tags available for fetching from confs.tech.
    // If you feel like one is missing, feel free to add one from https://github.com/tech-conferences/conference-data/tree/main/conferences
    private const SOURCE_AVAILABLE_TAGS = [
        'android',
        'clojure',
        'cpp',
        'css',
        'data',
        'devops',
        'dotnet',
        'elixir',
        'elm',
        'general',
        'golang',
        'graphql',
        'ios',
        'iot',
        'java',
        'javascript',
        'kotlin',
        'leadership',
        'networking',
        'php',
        'product',
        'python',
        'ruby',
        'rust',
        'scala',
        'security',
        'tech-comm',
        'typescript',
        'ux',
    ];

    private LocationGuesser $locationGuesser;
    private Filesystem $filesystem;
    private ConfTechCloner $confTechCloner;
    private LoggerInterface $logger;

    public function __construct(LocationGuesser $locationGuesser, Filesystem $filesystem, ConfTechCloner $confTechCloner, ?LoggerInterface $logger = null)
    {
        $this->locationGuesser = $locationGuesser;
        $this->filesystem = $filesystem;
        $this->confTechCloner = $confTechCloner;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param array<mixed> $configuration
     *
     * @return \Generator<Conference>
     */
    public function fetch(array $configuration = []): \Generator
    {
        if (0 === \count($configuration) || 0 === \count($configuration['tags'])) {
            $this->logger->warning(sprintf('The %s is not configured and will not fetch anything. Please add a configuration in the admin.', self::class));

            return;
        }

        $now = $configuration['now'] ??= new \DateTime();
        $conftechFile = $this->confTechCloner->clone();

        foreach ([$now->format('Y'), $now->format('Y') + 1] as $date) {
            foreach ($configuration['tags'] as $tag) {
                $path = sprintf('%s/%s/%s.json', $conftechFile, $date, $tag);
                if (!$this->filesystem->exists($path)) {
                    continue;
                }

                $conferences = json_decode(file_get_contents($path), true);

                foreach ($conferences as $conference) {
                    if ($conference['startDate'] > $now->format('Y-m-d')) {
                        $denormalizedConference = $this->denormalizeConference($conference, $tag);

                        if (!$denormalizedConference) {
                            continue;
                        }

                        yield $denormalizedConference;
                    }
                }
            }
        }
    }

    public function configureForm(FormBuilderInterface $formBuilder): void
    {
        $formBuilder
            ->add('tags', ChoiceType::class, [
                'label' => 'Tags',
                'choices' => array_combine(self::SOURCE_AVAILABLE_TAGS, self::SOURCE_AVAILABLE_TAGS),
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ]);
    }

    /** @param array<mixed> $rawConference */
    private function denormalizeConference(array $rawConference, string $tag): ?Conference
    {
        if (!$online = $rawConference['online'] ?? false) {
            $query = sprintf('%s %s', $rawConference['city'], 'U.S.A.' === $rawConference['country'] ? 'United States of America' : $rawConference['country']);
            $continent = $this->locationGuesser->getContinent($query);

            if (!$continent instanceof Continent || !$continent->getEnabled()) {
                return null;
            }
        }

        $startDate = new \DateTimeImmutable($rawConference['startDate']);

        $slug = Transliterator::transliterate(sprintf('%s %s %s', $rawConference['name'], $rawConference['city'] ?? 'online', $startDate->format('Y')));

        $conference = new Conference();
        $conference->setSource(self::SOURCE);
        $conference->setSlug($slug);
        $conference->setName($rawConference['name']);
        $conference->setStartAt($startDate);
        $conference->setSiteUrl($rawConference['url']);
        $conference->setOnline($online);
        $conference->addTag($tag);

        if ($online) {
            $conference->setCity('Online');
            $conference->setOnline(true);
        } else {
            $city = $rawConference['city'];
            $country = $this->locationGuesser->getCountry($city);
            $coords = $this->locationGuesser->getCoordinates($city);

            $conference->setCountry($country);
            $conference->setCoordinates($coords);
            $conference->setCity($city);
        }

        if (\array_key_exists('endDate', $rawConference)) {
            $endDate = new \DateTimeImmutable($rawConference['endDate']);
            $conference->setEndAt($endDate);
        }

        if (\array_key_exists('description', $rawConference)) {
            $conference->setDescription($rawConference['description']);
        }

        if (\array_key_exists('cfpUrl', $rawConference)) {
            $conference->setCfpUrl($rawConference['cfpUrl']);
        }

        if (\array_key_exists('cfpEndDate', $rawConference)) {
            $cfpEndAt = new \DateTimeImmutable($rawConference['cfpEndDate']);
            $conference->setCfpEndAt($cfpEndAt);
        }

        return $conference;
    }
}
