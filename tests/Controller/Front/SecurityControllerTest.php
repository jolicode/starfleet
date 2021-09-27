<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Controller\Front;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAdminCanAccessToAdmin()
    {
        $userProxy = UserFactory::createOne([
            'email' => 'admin@starfleet.app',
            'roles' => ['ROLE_ADMIN'],
            'name' => 'Admin',
            'password' => 'password',
        ]);

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->loginUser($userProxy->object());
        $client->followRedirects();

        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span.user-name', 'Admin');
    }

    public function testUserCannotAccessToAdmin()
    {
        $userProxy = UserFactory::createOne([
            'roles' => ['ROLE_USER'],
        ]);

        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($userProxy->object());

        $client->request('GET', '/admin/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testNotAuthenticatedUserCanNotAccessToAdmin()
    {
        $client = $this->createClient();
        $client->followRedirects();

        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }
}
