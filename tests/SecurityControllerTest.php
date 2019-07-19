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

use App\Tests\Functional\CreateAuthenticatedClientTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use CreateAuthenticatedClientTrait;

    public function testAuthenticateUserCanAccessToAdminPanel()
    {
        $client = static::createAuthenticatedClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.user-name', 'Admin');
    }
}
