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

use App\Entity\Talk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Talk|null find($id, $lockMode = null, $lockVersion = null)
 * @method Talk|null findOneBy(array $criteria, array $orderBy = null)
 * @method Talk[]    findAll()
 * @method Talk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TalkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Talk::class);
    }
}
