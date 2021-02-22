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
use Doctrine\Persistence\ManagerRegistry;

class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    /** @return array<mixed> */
    public function findEndingCfps(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $threshold = new \DateTime('+31 days');
        $threshold->setTime(0, 0, 0);

        return $this->createQueryBuilder('c')
            ->andWhere('c.cfpEndAt IS NOT NULL AND c.cfpEndAt >= :today AND c.cfpEndAt < :threshold')
            ->andWhere('c.excluded = :excluded')
            ->setParameter('excluded', false)
            ->setParameter('today', $today)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute()
            ;
    }

    /** @return array<mixed> */
    public function findAttendedConferences(): array
    {
        return $this->createAttendedQueryBuilder()
            ->getQuery()
            ->execute()
            ;
    }

    /** @return array<mixed> */
    public function findAttendedConferencesByTag(string $tag): array
    {
        return $this->createAttendedQueryBuilder()
            ->select('c')
            ->andWhere('CONTAINS(c.tags, :tagName) = true')
            ->setParameter('tagName', json_encode($tag))
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

    /** @return \Generator<Conference>|null */
    public function getNullCoordinatesConferences(): ?\Generator
    {
        $iterator = $this->createQueryBuilder('c')
            ->andWhere('c.online = :falseValue')
            ->setParameter('falseValue', false)
            ->andWhere('c.coordinates IS NULL')
            ->getQuery()
            ->iterate()
            ;

        foreach ($iterator as $conference) {
            yield $conference[0];
        }
    }

    public function findExistingConference(Conference $conference): ?Conference
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.startAt = :startAt')
            ->andWhere('c.endAt = :endAt')
            ->andWhere('levenshtein(c.name, :name) < 4')
            ->setParameters([
                'startAt' => $conference->getStartAt(),
                'endAt' => $conference->getEndAt(),
                'name' => $conference->getName(),
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    private function createAttendedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participations', 'p')
            ->andWhere('SIZE(c.participations) > 0')
            ->andWhere('CONTAINS(p.marking, :marking) = true')
            ->setParameter('marking', '{"validated": 1}')
            ->orderBy('c.startAt', 'ASC')
            ;
    }
}
