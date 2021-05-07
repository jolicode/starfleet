<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    public function testConferencesWithAcceptedParticipationsAreDisplayed()
    {
        $this->markTestIncomplete('This test needs to be rewritten.');
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#conferencesTabs', 'Incoming conferences');
        $this->assertSelectorTextContains('#conferencesTabs', '🔴 Live conferences! Find us there!');
        $this->assertSelectorTextContains('#conferencesTabs', 'Past conferences');
        $this->assertCount(2, $crawler->filter('div#future div.card'));
        $this->assertCount(1, $crawler->filter('div#live div.card'));
        $this->assertCount(1, $crawler->filter('div#past div.card'));
    }
}
