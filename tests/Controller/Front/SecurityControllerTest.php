<?php

namespace App\Tests\Controller\Front;

use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

class SecurityControllerTest extends AbstractStarfleetTest
{
    use Factories;

    public function testAdminCanAccessToAdmin()
    {
        $client = $this->getClient($this->getAdminUser());
        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span.user-name', 'Starfleet Admin');
    }

    public function testUserCannotAccessToAdmin()
    {
        $this->getClient()->request('GET', '/admin/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testNotAuthenticatedUserCanNotAccessToAdmin()
    {
        $this->ensureKernelShutdown();
        $client = $this->createClient();
        $client->followRedirects();

        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    protected function generateData()
    {
        // No specific data needed for these tests.
    }
}
