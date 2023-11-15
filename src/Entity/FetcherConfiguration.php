<?php

namespace App\Entity;

use App\Repository\FetcherConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=FetcherConfigurationRepository::class)
 *
 * @UniqueEntity("fetcherClass")
 */
class FetcherConfiguration
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="fetcher_class", type="string", length=255, unique=true)
     */
    private string $fetcherClass;

    /**
     * This is an array that will configure the way events are fetched.
     * All fetchers are different so there is no common configuration.
     *
     * @ORM\Column(type="jsonb")
     *
     * @var array<mixed>
     */
    private array $configuration = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $active = true;

    public function __construct(string $fetcherClass)
    {
        $this->fetcherClass = $fetcherClass;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFetcherClass(): string
    {
        return $this->fetcherClass;
    }

    public function setFetcherClass(string $fetcherClass): void
    {
        $this->fetcherClass = $fetcherClass;
    }

    /** @return array<mixed> */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /** @param array<mixed> $configuration */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
