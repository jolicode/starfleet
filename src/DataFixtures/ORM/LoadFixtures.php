<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataFixtures\ORM;

use App\EventListener\DoctrineEventSubscriber;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Finder\Finder;

class LoadFixtures implements FixtureInterface
{
    private $doctrineEventSubscriber;

    public function __construct(DoctrineEventSubscriber $doctrineEventSubscriber)
    {
        $this->doctrineEventSubscriber = $doctrineEventSubscriber;
    }

    public function load(ObjectManager $manager)
    {
        $loader = new NativeLoader();

        $finder = new Finder();
        $finder->files()->name('*.yml')->in(__DIR__);

        $events = $this->doctrineEventSubscriber->getSubscribedEvents();

        $eventManager = $manager->getEventManager();
        $eventManager->removeEventListener($events, $this->doctrineEventSubscriber);

        foreach ($finder as $file) {
            $objectSet = $loader->loadFile($file->getRealPath());

            foreach ($objectSet->getObjects() as $object) {
                $manager->persist($object);
            }
        }

        $manager->flush();
        $eventManager->addEventListener($events, $this->doctrineEventSubscriber);
    }
}
