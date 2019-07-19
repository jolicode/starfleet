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

use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    public function findEndingCfps(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $threshold = new \DateTime('+31 days');
        $threshold->setTime(0, 0, 0);

        return $this->createAttendedQueryBuilder()
            ->andWhere('c.cfpEndAt IS NOT NULL AND c.cfpEndAt >= :today AND c.cfpEndAt < :threshold')
            ->setParameter('today', $today)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute()
        ;
    }

    public function findAttendedConferences(): array
    {
        return $this->createAttendedQueryBuilder()
            ->getQuery()
            ->execute()
        ;
    }

    public function findAttendedConferencesByTag(string $tag): array
    {
        return $this->createAttendedQueryBuilder()
            ->leftJoin('c.tags', 't')
            ->andWhere('t.name = :tagName')
            ->setParameter('tagName', $tag)
            ->getQuery()
            ->execute()
        ;
    }

    public function findOneAttended(string $slug): ?Conference
    {
        return $this->createAttendedQueryBuilder()
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createAttendedQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.participations', 'p')
            ->andWhere('SIZE(c.participations) > 0')
            ->andWhere('CONTAINS(p.marking, :marking) = true')
            ->setParameter('marking', '{"validated": 1}')
            ->orderBy('c.startAt', 'ASC')
        ;

        return $qb;
    }
}
