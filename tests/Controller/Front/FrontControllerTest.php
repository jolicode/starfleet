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

use App\Enum\Workflow\Transition\Participation;
use App\Factory\ConferenceFactory;
use App\Factory\ParticipationFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FrontControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testConferencesWithAcceptedParticipationsAreDisplayed()
    {
        UserFactory::createMany(2);
        $pastConferenceProxy = ConferenceFactory::createOne([
            'name' => 'Past Conference',
            'excluded' => false,
            'startAt' => new \DateTime('-1 years'),
            'endAt' => new \DateTime('-1 years'),
        ]);
        $liveConferenceProxy = ConferenceFactory::createOne([
            'name' => 'Live Conference',
            'excluded' => false,
            'startAt' => new \DateTime(),
            'endAt' => new \DateTime('+1 days'),
        ]);
        $futureConferenceProxy = ConferenceFactory::createOne([
            'name' => 'Future Conference',
            'excluded' => false,
            'startAt' => new \DateTime('+1 years'),
            'endAt' => new \DateTime('+1 years'),
        ]);
        ParticipationFactory::createOne([
            'marking' => Participation::ACCEPTED,
            'conference' => ConferenceFactory::find($pastConferenceProxy),
        ]);
        ParticipationFactory::createOne([
            'marking' => Participation::ACCEPTED,
            'conference' => ConferenceFactory::find($liveConferenceProxy),
        ]);
        ParticipationFactory::createOne([
            'marking' => Participation::ACCEPTED,
            'conference' => ConferenceFactory::find($futureConferenceProxy),
        ]);

        $this->ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#conferencesTabs', 'Incoming conferences');
        $this->assertSelectorTextContains('#conferencesTabs', '🔴 Live conferences! Find us there!');
        $this->assertSelectorTextContains('#conferencesTabs', 'Past conferences');
        $this->assertCount(1, $crawler->filter('div#future div.card'));
        $this->assertCount(1, $crawler->filter('div#live div.card'));
        $this->assertCount(1, $crawler->filter('div#past div.card'));
    }
}
