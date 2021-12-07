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

use App\Entity\Participation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Participation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participation[]    findAll()
 * @method Participation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /** @return array<Participation> */
    public function findFutureParticipationsByUser(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(23, 59, 59);

        return $this->createStatusAndUserQueryBuilder($user, 'accepted')
            ->innerJoin('p.conference', 'c')
            ->andWhere('c.startAt > :today')
            ->setParameter('today', $today)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->execute()
        ;
    }

    /** @return array<Participation> */
    public function findPastParticipationsByUser(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        return $this->createStatusAndUserQueryBuilder($user, 'accepted')
            ->innerJoin('p.conference', 'c')
            ->andWhere('c.endAt < :today')
            ->setParameter('today', $today)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return array<Participation>
     */
    public function findPendingParticipationsByUser(User $user): array
    {
        return $this->createStatusAndUserQueryBuilder($user, 'pending')
            ->innerJoin('p.conference', 'c')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return array<Participation>
     */
    public function findRejectedParticipationsByUser(User $user): array
    {
        return $this->createStatusAndUserQueryBuilder($user, 'rejected')
            ->innerJoin('p.conference', 'c')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->execute()
        ;
    }

    private function createStatusAndUserQueryBuilder(User $user, string $status): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.participant = :user')
            ->andWhere('p.marking = :marking')
            ->setParameters(['user' => $user, 'marking' => $status])
        ;
    }
}
