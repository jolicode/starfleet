<?php

namespace App\Repository;

use App\Entity\Continent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Continent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Continent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Continent[]    findAll()
 * @method Continent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContinentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Continent::class);
    }

    /** @return array<Continent> */
    public function findAllAsKey(): array
    {
        $qb = $this->createQueryBuilder('c', 'c.name');

        return $qb->getQuery()->getResult();
    }
}
