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

use App\Entity\Submit;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use App\Repository\TalkRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserSubmitControllerTest extends WebTestCase
{
    private ?User $user = null;

    public function testSubmitsPageWork()
    {
        $client = $this->createClient();
        $submitRepository = static::$container->get(SubmitRepository::class);

        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/submits');
        self::assertResponseIsSuccessful();

        $submitsArray = [
            'pending' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING),
            'done' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE),
            'rejected' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED),
            'future' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED),
        ];

        foreach ($submitsArray as $state => $submits) {
            if (\count($submits) < 4) {
                self::assertCount(\count($submits), $crawler->filter("#{$state}-submits-block .user-items-list"));
            } else {
                self::assertSelectorExists("#{$state}-submits-block a", '...Show more');
                self::assertCount(3, $crawler->filter("#{$state}-submits-block .user-items-list"));
            }
        }
    }

    public function testSubmitsFormWork()
    {
        $client = $this->createClient();
        $submitRepository = static::$container->get(SubmitRepository::class);

        $client->loginUser($this->getUser());
        $client->request('GET', '/user/submits');

        $pendingSubmits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING);
        $preFormSubmitCount = \count($pendingSubmits);

        $this->createNewSubmit($client);

        self::assertCount(++$preFormSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING));
    }

    public function testActionsWork(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->followRedirects();
        $crawler = $client->request('GET', '/user/submits');
        $submitRepository = static::$container->get(SubmitRepository::class);

        $this->setSubmitAccepted($client, $crawler, $submitRepository);
        $this->setSubmitDone($client, $crawler, $submitRepository);
        $this->setSubmitRejected($client, $crawler, $submitRepository);
        $this->setSubmitPending($client, $crawler, $submitRepository);
    }

    public function testPendingSubmitsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/pending-submits');

        self::assertResponseIsSuccessful();
    }

    public function testAcceptedSubmitsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/future-submits');

        self::assertResponseIsSuccessful();
    }

    public function testRejectedSubmitsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/rejected-submits');

        self::assertResponseIsSuccessful();
    }

    public function testDoneSubmitsMorePageLoad()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', '/user/done-submits');

        self::assertResponseIsSuccessful();
    }

    private function setSubmitAccepted(KernelBrowser $client, Crawler $crawler, SubmitRepository $submitRepository)
    {
        $acceptedSubmits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED);
        $preActionSubmitCount = \count($acceptedSubmits);

        if (0 === \count($crawler->filter('#pending-submits-block'))) {
            $crawler = $this->createNewSubmit($client);
        }

        $crawler = $this->clickActionLink($crawler, $client, actionName: 'accepted');

        self::assertCount(++$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED));
    }

    private function setSubmitDone(KernelBrowser $client, Crawler $crawler, SubmitRepository $submitRepository)
    {
        $doneSubmits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE);
        $preActionSubmitCount = \count($doneSubmits);

        if (0 === \count($crawler->filter('#pending-submits-block'))) {
            $crawler = $this->createNewSubmit($client);
        }

        $crawler = $this->clickActionLink($crawler, $client, actionName: 'done');

        self::assertCount(++$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE));
    }

    private function setSubmitRejected(KernelBrowser $client, Crawler $crawler, SubmitRepository $submitRepository)
    {
        $rejectedSubmits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED);
        $preActionSubmitCount = \count($rejectedSubmits);

        if (0 === \count($crawler->filter('#pending-submits-block'))) {
            $crawler = $this->createNewSubmit($client);
        }

        $crawler = $this->clickActionLink($crawler, $client, actionName: 'rejected');

        self::assertCount(++$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED));
    }

    private function setSubmitPending(KernelBrowser $client, Crawler $crawler, SubmitRepository $submitRepository)
    {
        $pendingSubmits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING);
        $preActionSubmitCount = \count($pendingSubmits);

        if (0 === \count($crawler->filter('#future-submits-block'))) {
            $crawler = $this->createNewSubmit($client);
            $crawler = $this->clickActionLink($crawler, $client, actionName: 'accepted');
        }

        $crawler = $this->clickActionLink($crawler, $client, actionName: 'pending', blockName: 'future');

        self::assertCount(++$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING));
    }

    private function getUser(): User
    {
        if (null === $this->user) {
            $userRepository = static::$container->get(UserRepository::class);
            $this->user = $userRepository->findOneBy(['name' => 'User']);
        }

        return $this->user;
    }

    private function createNewSubmit(KernelBrowser $client): Crawler
    {
        $conferenceRepository = static::$container->get(ConferenceRepository::class);
        $talkRepository = static::$container->get(TalkRepository::class);

        $client->submitForm('submit_submit', [
            'submit[conference]' => $conferenceRepository->find(1)->getName(),
            'submit[talk]' => $talkRepository->find(1)->getId(),
            'submit[users]' => $this->getUser()->getId(),
        ]);

        return $client->request('GET', '/user/submits');
    }

    private function clickActionLink(Crawler $crawler, KernelBrowser $client, string $actionName, string $blockName = 'pending'): Crawler
    {
        $link = $crawler
            ->filter(sprintf(
                '#%s-submits-block a.action-%s',
                $blockName,
                $actionName
            ))
            ->first()
            ->link()
        ;

        return $client->click($link);
    }
}
