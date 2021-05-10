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

use App\Entity\Submit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Submit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Submit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Submit[]    findAll()
 * @method Submit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubmitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submit::class);
    }

    /** @return array<Submit> */
    public function findUserSubmitsByStatus(User $user, string $status): array
    {
        return $this->createUserQueryBuilder($user)
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->innerJoin('s.conference', 'c')
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->execute()
        ;
    }

    /** @return array<Submit> */
    public function findFutureUserSubmits(User $user): array
    {
        $today = new \DateTime();

        return $this->createUserQueryBuilder($user)
            ->innerJoin('s.conference', 'c')
            ->andWhere('c.startAt >= :today')
            ->setParameter('today', $today)
            ->andWhere('s.status = :accepted')
            ->setParameter('accepted', Submit::STATUS_ACCEPTED)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return array<Submit>
     */
    public function findUserSubmits(User $user): array
    {
        return $this->createUserQueryBuilder($user)
            ->getQuery()
            ->execute()
        ;
    }

    private function createUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.users', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
        ;
    }
}
