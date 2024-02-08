<?php

namespace App\Tests\Functional;

use App\Enum\Workflow\Transition\Participation;
use App\Factory\ConferenceFactory;
use App\Factory\ParticipationFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;
use Zenstruck\Foundry\Test\Factories;

class FrontControllerTest extends AbstractStarfleetTest
{
    use Factories;

    public function testConferencesWithAcceptedParticipationsAreDisplayed()
    {
        $crawler = $this->getClient()->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#conferencesTabs', 'Incoming conferences');
        $this->assertSelectorTextContains('#conferencesTabs', '🔴 Live conferences! Find us there!');
        $this->assertSelectorTextContains('#conferencesTabs', 'Past conferences');
        $this->assertCount(1, $crawler->filter('div#future-participations-block article.card'));
        $this->assertCount(1, $crawler->filter('div#pending-participations-block article.card'));
        $this->assertCount(1, $crawler->filter('div#past-participations-block article.card'));
    }

    protected function generateData()
    {
        UserFactory::createOne();
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
    }
}
