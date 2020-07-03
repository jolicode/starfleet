<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tags = [
            'CSS',
            'DevOps',
            'General',
            'Go',
            'HTML',
            'Javascript',
            'PHP',
            'React Native',
            'Rust',
            'Security',
        ];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tag->setSelected(true);
            $tag->setCreatedAt((new \DateTime()));
            $tag->setUpdatedAt((new \DateTime()));

            $manager->persist($tag);
        }

        $manager->flush();
    }
}
