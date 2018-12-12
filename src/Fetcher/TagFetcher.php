<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Fetcher;

use App\Entity\Tag;
use App\Enum\TagEnum;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TagFetcher
{
    private $em;
    private $repository;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $this->em->getRepository(Tag::class);
    }

    public function FetchTag()
    {
        $tags = new TagEnum();
        $tags = $tags::TAGS;

        $allTag = [];

        foreach ($tags as $cat => $tagsList) {
            foreach ($tagsList as $tag) {
                $tagFinder = $this->repository->findOneBy([
                'name' => $tag,
            ]);

                if (!$tagFinder) {
                    $tagSolo = new Tag();
                    $tagSolo->setName($tag);
                    $this->em->persist($tagSolo);
                }
            }
        }

        $this->em->flush();

        return 'done ! 😉';
    }
}
