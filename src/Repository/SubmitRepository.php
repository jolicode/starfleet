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
 * @method null|Submit find($id, $lockMode = null, $lockVersion = null)
 * @method null|Submit findOneBy(array $criteria, array $orderBy = null)
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
            ->orderBy('c.id', 'DESC')
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

    public function updateDoneSubmits(): void
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        $this
            ->createQueryBuilder('s')
            ->update()
            ->set('s.status', ':status_done')
            ->andWhere('s.conference IN (SELECT c.id FROM App\Entity\Conference c WHERE c.id = s.conference AND c.endAt < :today)')
            ->andWhere('s.status = :status_accepted')
            ->setParameters([
                'status_done' => Submit::STATUS_DONE,
                'status_accepted' => Submit::STATUS_ACCEPTED,
                'today' => $today,
            ])
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
