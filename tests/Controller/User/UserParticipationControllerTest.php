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

use App\Entity\Participation;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use App\Repository\ParticipationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserParticipationControllerTest extends WebTestCase
{
    private ?User $user = null;

    public function testParticipationsPageWork()
    {
        $client = $this->createClient();
        $participationRepository = static::$container->get(ParticipationRepository::class);

        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/participations');
        self::assertResponseIsSuccessful();

        $participationsArray = [
            'pending' => $participationRepository->findPendingParticipationsByUser($this->getUser()),
            'past' => $participationRepository->findPastParticipationsByUser($this->getUser()),
            'rejected' => $participationRepository->findRejectedParticipationsByUser($this->getUser()),
            'future' => $participationRepository->findFutureParticipationsByUser($this->getUser()),
        ];

        foreach ($participationsArray as $state => $participations) {
            if (\count($participations) < 4) {
                self::assertCount(\count($participations), $crawler->filter("#{$state}-participations-block .user-items-list"));
            } else {
                self::assertSelectorExists("#{$state}-participations-block a", '...Show more');
                self::assertCount(3, $crawler->filter("#{$state}-participations-block .user-items-list"));
            }
        }
    }

    public function testParticipationsFormWork()
    {
        $client = $this->createClient();
        $participationRepository = static::$container->get(ParticipationRepository::class);

        $client->loginUser($this->getUser());
        $client->request('GET', '/user/participations');

        $pendingParticipations = $participationRepository->findPendingParticipationsByUser($this->getUser());
        $preFormParticipationCount = \count($pendingParticipations);

        $this->createNewParticipation($client);

        self::assertCount(++$preFormParticipationCount, $participationRepository->findPendingParticipationsByUser($this->getUser()));
    }

    public function testCancelParticipation()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->followRedirects();
        $crawler = $client->request('GET', '/user/participations');

        $participationRepository = static::$container->get(ParticipationRepository::class);
        $pendingParticipations = $participationRepository->findPendingParticipationsByUser($this->getUser());
        $preCancelCount = \count($pendingParticipations);

        if (0 === \count($crawler->filter('#pending-participations-block'))) {
            $crawler = $this->createNewParticipation($client);
        }

        $link = $crawler
            ->filter('#pending-participations-block a.action-cancel')
            ->first()
            ->link()
        ;
        $client->click($link);

        self::assertCount(--$preCancelCount, $participationRepository->findPendingParticipationsByUser($this->getUser()));
    }

    public function testPendingParticipationsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/pending-participations');

        self::assertResponseIsSuccessful();
    }

    public function testAcceptedParticipationsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/future-participations');

        self::assertResponseIsSuccessful();
    }

    public function testRejectedParticipationsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/rejected-participations');

        self::assertResponseIsSuccessful();
    }

    public function testPastParticipationsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/past-participations');

        self::assertResponseIsSuccessful();
    }

    public function testEditParticipationPageWork()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());

        $participationRepository = static::$container->get(ParticipationRepository::class);
        $participation = $participationRepository->findOneBy(['participant' => $this->getUser()]);

        $client->request('GET', sprintf('/user/participation-edit/%d', $participation->getId()));
        self::assertResponseIsSuccessful();

        $conferenceRepository = static::$container->get(ConferenceRepository::class);
        $participation->setConference($conferenceRepository->find(2));
        $participation->setTransportStatus(Participation::STATUS_NOT_NEEDED);
        $participation->setHotelStatus(Participation::STATUS_NEEDED);
        $participation->setConferenceTicketStatus(Participation::STATUS_NOT_NEEDED);

        $client->submitForm('edit_participation', [
            'participation[conference]' => $conferenceRepository->find(1)->getName(),
            'participation[transportStatus]' => Participation::STATUS_NEEDED,
            'participation[hotelStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[conferenceTicketStatus]' => Participation::STATUS_BOOKED,
        ]);

        $participation = $participationRepository->find($participation->getId());

        self::assertSame($conferenceRepository->find(1), $participation->getConference());
        self::assertSame(Participation::STATUS_NEEDED, $participation->getTransportStatus());
        self::assertSame(Participation::STATUS_NOT_NEEDED, $participation->getHotelStatus());
        self::assertSame(Participation::STATUS_BOOKED, $participation->getConferenceTicketStatus());
    }

    public function testEditParticipationLinkWork()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/participations');

        if (!$crawler->filter('a.action-edit')->count()) {
            $this->createNewParticipation($client);
        }

        $link = $crawler
            ->filter('a.action-edit')
            ->first()
            ->link()
        ;
        $client->click($link);

        self::assertResponseIsSuccessful();
    }

    private function createNewParticipation(KernelBrowser $client): Crawler
    {
        $conferenceRepository = static::$container->get(ConferenceRepository::class);

        $client->submitForm('submit_participation', [
            'participation[conference]' => $conferenceRepository->find(1)->getName(),
            'participation[transportStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[hotelStatus]' => Participation::STATUS_NOT_NEEDED,
            'participation[conferenceTicketStatus]' => Participation::STATUS_NOT_NEEDED,
        ]);

        return $client->request('GET', '/user/participations');
    }

    private function getUser(): User
    {
        if (null === $this->user) {
            $userRepository = static::$container->get(UserRepository::class);
            $this->user = $userRepository->findOneBy(['name' => 'User']);
        }

        return $this->user;
    }
}
