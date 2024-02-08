<?php

namespace App\Tests\Controller\UserAccount;

use App\Factory\ConferenceFactory;
use App\Tests\AbstractStarfleetTest;

/**
 * @group user_account
 */
class FutureConferencesControllerTest extends AbstractStarfleetTest
{
    private string $conferencesUrl = '/user/future-conferences';

    public function testFutureConferencesPageLoad()
    {
        $this->getClient()->request('GET', $this->conferencesUrl);
        self::assertResponseIsSuccessful();
    }

    public function testConferencesAreDisplayed()
    {
        $crawler = $this->getClient()->request('GET', $this->conferencesUrl);

        $featuredConferencesCount = \count(ConferenceFactory::findBy([
            'featured' => true,
        ]));

        $regularConferencesCount = \count(ConferenceFactory::findBy([
            'featured' => false,
        ]));

        self::assertCount($featuredConferencesCount, $crawler->filter('div#featured-conferences-block article.card'));
        self::assertCount($regularConferencesCount, $crawler->filter('div#regular-conferences-block article.card'));
    }

    public function testAskParticipationWork()
    {
        $this->getClient()->request('GET', $this->conferencesUrl);
        $this->getClient()->submitForm('Ask Participation');

        self::assertResponseIsSuccessful();
    }

    public function testSubmitTalkWork()
    {
        $this->getClient()->request('GET', $this->conferencesUrl);
        $this->getClient()->submitForm('Submit a Talk');

        self::assertResponseIsSuccessful();
    }

    protected function generateData()
    {
        ConferenceFactory::createMany(3, [
            'featured' => true,
            'startAt' => new \DateTime('+1 days'),
            'endAt' => new \DateTime('+1 days'),
        ]);

        ConferenceFactory::createMany(3, [
            'featured' => false,
            'startAt' => new \DateTime('+1 days'),
            'endAt' => new \DateTime('+1 days'),
        ]);
    }
}
