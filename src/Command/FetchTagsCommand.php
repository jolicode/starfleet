<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Tag;
use App\Enum\TagEnum;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchTagsCommand extends Command
{
    private $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet:tags:fetch');
        $this->setDescription('Fetch tags from TagEnum Class');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = TagEnum::toArray();
        $tagAddedCounter = 0;
        foreach ($tags as $keyTag => $valueTag) {
            $tagFinder = $this->em->getRepository(Tag::class)->findOneBy([
                'name' => $valueTag,
            ]);

            if (!$tagFinder) {
                $tag = new Tag();
                $tag->setName($valueTag);
                $this->em->persist($tag);
                ++$tagAddedCounter;
            }
        }
        $this->em->flush();

        $output->writeln('You add '.$tagAddedCounter.' tag(s)');
    }
}
