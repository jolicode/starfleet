<?php

namespace App\Repository;

use App\Entity\ConferenceFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConferenceFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConferenceFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConferenceFilter[]    findAll()
 * @method ConferenceFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConferenceFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConferenceFilter::class);
    }
}
