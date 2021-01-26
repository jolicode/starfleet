<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

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

    public function findOneOrCreate(string $className): FetcherConfiguration
    {
        $config = $this->findOneBy(['fetcherClass' => $className]);

        if (null === $config) {
            $config = new FetcherConfiguration($className);
        }

        return $config;
    }
}
