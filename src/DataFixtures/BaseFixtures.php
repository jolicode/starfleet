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

use App\Entity\Conference;
use App\Entity\Participation;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class BaseFixtures extends Fixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function addTalk(array $description = []): Talk
    {
        $talk = FixtureBuilder::createTalk($description);
        $this->manager->persist($talk);

        return $talk;
    }

    protected function addSubmit(array $description = []): Submit
    {
        $submit = FixtureBuilder::createSubmit($description);
        $this->manager->persist($submit);

        return $submit;
    }

    protected function addUser(array $description = []): User
    {
        $user = FixtureBuilder::createUser($description, $this->container->get('security.password_encoder'));
        $this->manager->persist($user);

        return $user;
    }

    protected function addConference(array $description = []): Conference
    {
        $conference = FixtureBuilder::createConference($description);
        $this->manager->persist($conference);

        return $conference;
    }

    protected function addParticipation(array $description = []): Participation
    {
        $participation = FixtureBuilder::createParticipation($description);
        $this->manager->persist($participation);

        return $participation;
    }
}
