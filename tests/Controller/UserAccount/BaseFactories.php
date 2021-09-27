<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Controller\UserAccount;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class BaseFactories extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private ?Proxy $user = null;
    private ?KernelBrowser $client = null;

    protected function getTestUser(): User
    {
        if (!$this->user) {
            $user = UserFactory::createOne([
                'name' => 'My Test User',
                'email' => 'user@starfleet.app',
                'password' => 'user',
            ]);

            $this->user = $user;
        }

        return $this->user->object();
    }

    protected function getClient(): KernelBrowser
    {
        if (!$this->client) {
            $this->ensureKernelShutdown();
            $this->client = $this->createClient();
            $this->client->followRedirects();
            $this->client->loginUser($this->getTestUser());
        }

        return $this->client;
    }

    protected function getAdminUser(): User
    {
        $this->getTestUser()
            ->addRole('ROLE_ADMIN')
            ->setName('My Admin User')
            ->setEmail('admin@starfleet.app')
            ->setPassword('admin')
        ;
        $this->user->save();

        return $this->user->object();
    }
}
