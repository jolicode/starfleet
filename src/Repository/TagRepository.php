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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Psr\Log\LoggerInterface;

class TagRepository extends EntityRepository
{
    private $logger;

    public function __construct($em, Mapping\ClassMetadata $class, LoggerInterface $logger = null)
    {
        parent::__construct($em, $class);
        $this->logger = $logger;
    }
}
