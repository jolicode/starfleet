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

use App\Entity\Submit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SubmitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $submit = new Submit();
        $submit->setSubmittedAt(new \DateTime());
        $submit->setStatus('pending');
        $submit->addUser($this->getReference('user'));
        $submit->addUser($this->getReference('admin'));
        $submit->setTalk($this->getReference('talk'));
        $manager->persist($submit);

        $submit = new Submit();
        $submit->setSubmittedAt(new \DateTime());
        $submit->setStatus('pending');
        $submit->addUser($this->getReference('user'));
        $submit->setTalk($this->getReference('talk2'));
        $manager->persist($submit);

        $submit = new Submit();
        $submit->setSubmittedAt(new \DateTime());
        $submit->setStatus('pending');
        $submit->addUser($this->getReference('admin'));
        $submit->setTalk($this->getReference('talk3'));
        $manager->persist($submit);

        $submit = new Submit();
        $submit->setSubmittedAt(new \DateTime());
        $submit->setStatus('pending');
        $submit->addUser($this->getReference('admin'));
        $submit->setTalk($this->getReference('talk'));
        $manager->persist($submit);

        $submit = new Submit();
        $submit->setSubmittedAt(new \DateTime());
        $submit->setStatus('pending');
        $submit->addUser($this->getReference('user2'));
        $submit->addUser($this->getReference('admin'));
        $submit->setTalk($this->getReference('talk'));
        $manager->persist($submit);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [TalkFixtures::class, UserFixtures::class];
    }
}
