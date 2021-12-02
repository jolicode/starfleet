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

use App\Entity\ConferenceFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|ConferenceFilter find($id, $lockMode = null, $lockVersion = null)
 * @method null|ConferenceFilter findOneBy(array $criteria, array $orderBy = null)
 * @method ConferenceFilter[]    findAll()
 * @method ConferenceFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConferenceFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConferenceFilter::class);
    }
}
