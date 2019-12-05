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

use App\Entity\ExcludedTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ExcludedTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExcludedTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExcludedTag[]    findAll()
 * @method ExcludedTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcludedTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcludedTag::class);
    }
}
