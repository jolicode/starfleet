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

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TagRepository extends ServiceEntityRepository
{
    private $logger;

    public function __construct(RegistryInterface $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Tag::class);

        $this->logger = $logger;
    }
}
