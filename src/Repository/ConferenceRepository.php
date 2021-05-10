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
use App\Entity\User;
use App\Enum\Workflow\Transition\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    public function getFutureConferencesQueryBuilder(): QueryBuilder
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        return $this->createQueryBuilder('c')
            ->andWhere('c.startAt >= :today')
            ->andWhere('c.excluded = :excluded')
            ->setParameter('excluded', false)
            ->setParameter('today', $today)
        ;
    }

    /** @return array<mixed> */
    public function findEndingCfps(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        $threshold = new \DateTime('+31 days');
        $threshold->setTime(0, 0);

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
    public function getDailyConferences(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        $threshold = new \DateTime();
        $threshold->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.createdAt >= :today AND c.createdAt <= :threshold')
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

    /** @return \Generator<Conference>|null */
    public function getNullCoordinatesConferences(): ?\Generator
    {
        $iterator = $this->createQueryBuilder('c')
            ->andWhere('c.online = :falseValue')
            ->setParameter('falseValue', false)
            ->andWhere('c.coordinates IS NULL')
            ->getQuery()
            ->toIterable()
        ;

        foreach ($iterator as $conference) {
            yield $conference[0];
        }
    }

    public function findExistingConference(Conference $conference): ?Conference
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.startAt = :startAt')
            ->andWhere('levenshtein(c.name, :name) < 4')
            ->setParameters([
                'startAt' => $conference->getStartAt(),
                'name' => $conference->getName(),
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /** @return array<mixed> */
    public function getEndingCfpsByRemainingDays(): array
    {
        $daysRemaining0 = [];
        $daysRemaining1 = [];
        $daysRemaining5 = [];
        $daysRemaining10 = [];
        $daysRemaining20 = [];
        $daysRemaining30 = [];

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $conferences = $this->findEndingCfps();

        foreach ($conferences as $conference) {
            $remainingDays = (int) ($conference->getCfpEndAt()->diff($today)->format('%a'));

            match ($remainingDays) {
                0 => $daysRemaining0[] = $conference,
                1 => $daysRemaining1[] = $conference,
                5 => $daysRemaining5[] = $conference,
                10 => $daysRemaining10[] = $conference,
                20 => $daysRemaining20[] = $conference,
                30 => $daysRemaining30[] = $conference,
                default => null,
            };
        }

        if (!array_merge($daysRemaining0, $daysRemaining1, $daysRemaining5, $daysRemaining10, $daysRemaining20, $daysRemaining30)) {
            return [];
        }

        return [
            0 => $daysRemaining0,
            1 => $daysRemaining1,
            5 => $daysRemaining5,
            10 => $daysRemaining10,
            20 => $daysRemaining20,
            30 => $daysRemaining30,
        ];
    }

    /** @return array<Conference> */
    public function findAttentedConferencesByUser(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        return $this->createAttendedQueryBuilder()
            ->select('c')
            ->andWhere('p.participant = :user')
            ->andWhere('c.startAt < :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->getQuery()
            ->execute()
        ;
    }

    private function createAttendedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participations', 'p')
            ->andWhere('SIZE(c.participations) > 0')
            ->andWhere('p.marking = :marking')
            ->andWhere('c.excluded = :excluded')
            ->setParameter('marking', Participation::ACCEPTED)
            ->setParameter('excluded', false)
            ->orderBy('c.startAt', 'ASC')
        ;
    }
}
