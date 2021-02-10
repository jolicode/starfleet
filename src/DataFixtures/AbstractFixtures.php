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

abstract class AbstractFixtures extends Fixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
    }

    /** @param array<string,mixed> $description */
    protected function addTalk(array $description = []): Talk
    {
        $talk = FixtureBuilder::createTalk($description);
        $this->manager->persist($talk);

        return $talk;
    }

    /** @param array<string,mixed> $description */
    protected function addSubmit(array $description = []): Submit
    {
        $submit = FixtureBuilder::createSubmit($description);
        $this->manager->persist($submit);

        return $submit;
    }

    /** @param array<string,mixed> $description */
    protected function addUser(array $description = []): User
    {
        $user = FixtureBuilder::createUser($description, $this->container->get('security.password_encoder'));
        $this->manager->persist($user);

        return $user;
    }

    /** @param array<string,mixed> $description */
    protected function addConference(array $description = []): Conference
    {
        $conference = FixtureBuilder::createConference($description);
        $this->manager->persist($conference);

        return $conference;
    }

    /** @param array<string,mixed> $description */
    protected function addParticipation(array $description = []): Participation
    {
        $participation = FixtureBuilder::createParticipation($description);
        $this->manager->persist($participation);

        return $participation;
    }
}
