<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Controller\User;

use App\Factory\ConferenceFactory;

class FutureConferencesControllerTest extends BaseFactories
{
    private string $conferencesUrl = '/user/future-conferences';

    public function testFutureConferencesPageLoad()
    {
        $this->getClient()->request('GET', $this->conferencesUrl);
        self::assertResponseIsSuccessful();
    }

    public function testConferencesAreDisplayed()
    {
        ConferenceFactory::createMany(3, [
            'featured' => true,
            'startAt' => new \DateTime('+2 days'),
            'endAt' => new \DateTime('+5 days'),
        ]);

        ConferenceFactory::createMany(5, [
            'featured' => false,
            'startAt' => new \DateTime('+2 days'),
            'endAt' => new \DateTime('+5 days'),
        ]);

        $crawler = $this->getClient()->request('GET', $this->conferencesUrl);

        self::assertCount(3, $crawler->filter('div#featured-conferences-block div.conference-card'));
        self::assertCount(5, $crawler->filter('div#regular-conferences-block div.conference-card'));
    }

    public function testAskParticipationWork()
    {
        ConferenceFactory::createOne([
            'startAt' => new \DateTime('+2 days'),
            'endAt' => new \DateTime('+5 days'),
        ]);

        $this->getClient()->request('GET', $this->conferencesUrl);
        $this->getClient()->submitForm('Ask Participation');

        self::assertResponseIsSuccessful();
    }

    public function testSubmitTalkWork()
    {
        ConferenceFactory::createOne([
            'startAt' => new \DateTime('+2 days'),
            'endAt' => new \DateTime('+5 days'),
            'cfpEndAt' => new \DateTime('+5 days'),
        ]);

        $this->getClient()->request('GET', $this->conferencesUrl);
        $this->getClient()->submitForm('Submit a Talk');

        self::assertResponseIsSuccessful();
    }
}
