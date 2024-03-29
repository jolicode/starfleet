<?php

namespace App\Repository;

use App\Entity\FetcherConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FetcherConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method FetcherConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method FetcherConfiguration[]    findAll()
 * @method FetcherConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FetcherConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FetcherConfiguration::class);
    }

    public function findOneOrCreate(string $fetcherShortClass): FetcherConfiguration
    {
        $config = $this->findOneBy(['fetcherClass' => $fetcherShortClass]);

        if (null === $config) {
            $config = new FetcherConfiguration($fetcherShortClass);
        }

        return $config;
    }
}
