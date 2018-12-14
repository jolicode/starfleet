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

class TagRepository extends EntityRepository
{
    /**
     * @param string $tagName
     *
     * @return Tag
     */
    public function getTagByName(string $tagName): Tag
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.name = :name')
            ->setParameter('name', $tagName)
            ->getQuery()
            ->execute();

        return $qb[0];
    }

    public function getTagsBySelected(): array
    {
        return $qb = $this->createQueryBuilder('t')
            ->where('t.selected = :selected')
            ->setParameter('selected', true)
            ->getQuery()
            ->execute();
    }
}
