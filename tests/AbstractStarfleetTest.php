<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractStarfleetTest extends WebTestCase
{
    use Factories;

    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
        $this->generateData();
    }

    protected function getClient(?User $user = null)
    {
        if (!$this->client) {
            $user = $user ?: $this->getTestUser();
            $this->ensureKernelShutdown();
            $this->client = $this->createClient();
            $this->client->followRedirects();
            $this->client->loginUser($user);
        }

        return $this->client;
    }

    protected function getTestUser()
    {
        $user = UserFactory::repository()->findOneBy([
            'email' => 'user@starfleet.app',
        ]);

        if (!$user) {
            $user = UserFactory::createOne([
                'name' => 'Starfleet User',
                'email' => 'user@starfleet.app',
                'password' => 'password',
            ]);
        }

        return $user->object();
    }

    protected function getAdminUser()
    {
        $user = UserFactory::repository()->findOneBy([
            'email' => 'admin@starfleet.app',
        ]);

        if (!$user) {
            $user = UserFactory::createOne([
                'name' => 'Starfleet Admin',
                'email' => 'admin@starfleet.app',
                'password' => 'password',
                'roles' => ['ROLE_ADMIN'],
            ]);
        }

        return $user->object();
    }

    /**
     * This method must create all the entities your test will need.
     */
    abstract protected function generateData();

    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
