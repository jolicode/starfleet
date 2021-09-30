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

use App\Entity\Participation;
use App\Enum\Workflow\Transition\Participation as TransitionParticipation;
use App\Factory\ConferenceFactory;
use App\Factory\ParticipationFactory;
use App\Repository\ParticipationRepository;
use App\Tests\AbstractStarfleetTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @group user_account
 */
class ParticipationControllerTest extends AbstractStarfleetTest
{
    /** @dataProvider provideRoutes */
    public function testAllPagesLoad(string $route)
    {
        $this->getClient()->request('GET', $route);

        self::assertResponseIsSuccessful();
    }

    public function testParticipationsPageWork()
    {
        $crawler = $this->getClient()->request('GET', '/user/participations');

        self::assertCount(2, $crawler->filter('#pending-participations-block .card'));
        self::assertCount(1, $crawler->filter('#rejected-participations-block .card'));
        self::assertCount(2, $crawler->filter('#future-participations-block .card'));

        self::assertSelectorExists('#past-participations-block a', '...Show more');
        self::assertCount(3, $crawler->filter('#past-participations-block .card'));
    }

    public function testParticipationsFormWork()
    {
        $conference = ConferenceFactory::createOne([
            'name' => 'Future Conference',
            'startAt' => new \DateTime('+10 days'),
            'endAt' => new \DateTime('+12 days'),
        ]);
        $preSubmitCount = \count(ParticipationFactory::all());

        $this->getClient()->request('GET', '/user/participations');
        $this->getClient()->submitForm('submit_participation', [
            'participation[conference]' => $conference->getName(),
            'participation[transportStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[hotelStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[conferenceTicketStatus]' => Participation::STATUS_NOT_NEEDED,
        ]);

        $allParticipations = ParticipationFactory::all();

        self::assertCount($preSubmitCount + 1, $allParticipations);
        self::assertSame($this->getTestUser(), $allParticipations[$preSubmitCount]->getParticipant());
    }

    public function testEditParticipationPageWork()
    {
        $conference = ConferenceFactory::createOne(['name' => 'My Test Conference']);
        $participation = ParticipationFactory::createOne([
                'conference' => $conference,
                'participant' => $this->getTestUser(),
                'transportStatus' => Participation::STATUS_NOT_NEEDED,
                'hotelStatus' => Participation::STATUS_NOT_NEEDED,
                'conferenceTicketStatus' => Participation::STATUS_NOT_NEEDED,
            ])
        ;
        $conference->addParticipation($participation->object());

        $this->getClient()->request('GET', sprintf('/user/participation-edit/%d', $participation->getId()));
        self::assertResponseIsSuccessful();

        $this->getClient()->submitForm('edit_participation', [
            'participation[conference]' => $conference->getName(),
            'participation[transportStatus]' => Participation::STATUS_NEEDED,
            'participation[hotelStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[conferenceTicketStatus]' => Participation::STATUS_BOOKED,
        ]);

        self::assertSame(Participation::STATUS_NEEDED, $participation->object()->getTransportStatus());
        self::assertSame(Participation::STATUS_NOT_NEEDED, $participation->object()->getHotelStatus());
        self::assertSame(Participation::STATUS_BOOKED, $participation->object()->getConferenceTicketStatus());
    }

    /** @dataProvider provideActionRoutes */
    public function testAllEditParticipationLinksWork(string $route)
    {
        $this->getClient()->request('GET', $route);
        $this->getClient()->submitForm('Edit Participation');

        self::assertResponseIsSuccessful();
    }

    /** @dataProvider provideActionRoutes */
    public function testAllCancelParticipationLinksWork(string $route)
    {
        $crawler = $this->getClient()->request('GET', $route);

        if ('/user/participations' === $route) {
            $this->mainPageCancelLink($this->getClient(), $crawler);

            return;
        }

        $preCancelCount = \count($crawler->filter('form.action-cancel'));
        $this->getClient()->submitForm('Cancel Participation');
        $crawler = $this->getClient()->request('GET', $route);

        self::assertCount(--$preCancelCount, $crawler->filter('form.action-cancel'));
    }

    public function provideRoutes(): iterable
    {
        yield ['/user/participations'];
        yield ['/user/pending-participations'];
        yield ['/user/rejected-participations'];
        yield ['/user/past-participations'];
        yield ['/user/future-participations'];
    }

    public function provideActionRoutes(): iterable
    {
        yield ['/user/participations'];
        yield ['/user/pending-participations'];
        yield ['/user/future-participations'];
    }

    /** @dataProvider provideButtonsText */
    public function testNavLinksWork(string $buttonText)
    {
        foreach ($this->provideRoutes() as $route) {
            $crawler = $this->getClient()->request('GET', $route[0]);
            $this->getClient()->click($crawler->selectLink($buttonText)->link());

            self::assertResponseIsSuccessful();
        }
    }

    public function provideButtonsText()
    {
        yield ['Back Home'];
        yield ['Submits'];
        yield ['Talks'];
        yield ['Edit Profile'];
    }

    public function testParticipationCancelCsrfProtection()
    {
        $participation = ParticipationFactory::createOne([
            'participant' => $this->getTestUser(),
            'conference' => ConferenceFactory::createOne(),
        ]);

        $this->getClient()->request('POST', sprintf(
            '/user/participation-cancel/%d',
            $participation->getId(),
        ));

        self::assertResponseStatusCodeSame(403);
    }

    private function mainPageCancelLink(KernelBrowser $client, Crawler $crawler)
    {
        $participationRepository = $this->getContainer()->get(ParticipationRepository::class);
        $participations = $participationRepository->findPendingParticipationsByUser($this->getTestUser());
        $preCancelCount = \count($participations);

        $form = $crawler
            ->filter('#pending-participations-block form.action-cancel')
            ->first()
            ->form()
        ;
        $client->click($form);

        self::assertCount(--$preCancelCount, $participationRepository->findPendingParticipationsByUser($this->getTestUser()));
    }

    protected function generateData()
    {
        ParticipationFactory::createMany(2, [
            'participant' => $this->getTestUser(),
            'marking' => TransitionParticipation::PENDING,
            'conference' => ConferenceFactory::createOne([
                'startAt' => new \DateTime('+1 years'),
                'endAt' => new \DateTime('+1 years'),
            ]),
        ]);
        ParticipationFactory::createMany(1, [
            'participant' => $this->getTestUser(),
            'marking' => TransitionParticipation::REJECTED,
            'conference' => ConferenceFactory::createOne([
                'startAt' => new \DateTime('+1 years'),
                'endAt' => new \DateTime('+1 years'),
            ]),
        ]);
        ParticipationFactory::createMany(5, [
            'participant' => $this->getTestUser(),
            'marking' => TransitionParticipation::ACCEPTED,
            'conference' => ConferenceFactory::createOne([
                'startAt' => new \DateTime('-1 years'),
                'endAt' => new \DateTime('-1 years'),
            ]),
        ]);
        ParticipationFactory::createMany(2, [
            'participant' => $this->getTestUser(),
            'marking' => TransitionParticipation::ACCEPTED,
            'conference' => ConferenceFactory::createOne([
                'startAt' => new \DateTime('+1 years'),
                'endAt' => new \DateTime('+1 years'),
            ]),
        ]);
    }
}
