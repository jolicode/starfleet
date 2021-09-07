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

use App\Entity\Notifications\AbstractNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbstractNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractNotification[]    findAll()
 * @method AbstractNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractNotification::class);
    }

    public function markAllAsReadForUser(User $user)
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.read', ':newRead')
            ->andWhere('n.targetUser = :user')
            ->andWhere('n.read = :currentRead')
            ->setParameters([
                'newRead' => true,
                'currentRead' => false,
                'user' => $user,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    public function getAllUnreadForUser(User $user)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.read = :notRead')
            ->andWhere('n.targetUser = :user')
            ->setParameters([
                'notRead' => false,
                'user' => $user,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
