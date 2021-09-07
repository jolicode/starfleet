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

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Notifications\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function markAllAsRead(User $user)
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.read', ':true')
            ->andWhere('n.targetUser = :user')
            ->andWhere('n.read = :false')
            ->setParameters([
                'true' => true,
                'false' => false,
                'user' => $user,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
