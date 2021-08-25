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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserSubmitControllerTest extends WebTestCase
{
    private ?User $user = null;

    /** @dataProvider provideRoutes */
    public function testAllPagesLoad(string $route)
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', $route);

        self::assertResponseIsSuccessful();
    }

    public function provideRoutes(): iterable
    {
        yield ['/user/submits'];
        yield ['/user/pending-submits'];
        yield ['/user/rejected-submits'];
        yield ['/user/done-submits'];
        yield ['/user/accepted-submits'];
    }

    public function testSubmitsPageWork()
    {
        $client = $this->createClient();
        $submitRepository = static::$container->get(SubmitRepository::class);

        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/submits');

        $submitsArray = [
            'pending' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING),
            'done' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE),
            'rejected' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED),
            'future' => $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED),
        ];

        foreach ($submitsArray as $state => $submits) {
            if (\count($submits) < 4) {
                self::assertCount(\count($submits), $crawler->filter("#{$state}-submits-block .card"));
            } else {
                self::assertSelectorExists("#{$state}-submits-block a", '...Show more');
                self::assertCount(3, $crawler->filter("#{$state}-submits-block .card"));
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

        $conferenceRepository = static::$container->get(ConferenceRepository::class);
        $talkRepository = static::$container->get(TalkRepository::class);

        $client->submitForm('submit_submit', [
            'submit[conference]' => $conferenceRepository->find(1)->getName(),
            'submit[talk]' => $talkRepository->find(1)->getId(),
            'submit[users]' => $this->getUser()->getId(),
        ]);
        $client->request('GET', '/user/submits');

        self::assertCount(++$preFormSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING));
    }

    /** @dataProvider provideActions */
    public function testActions(string $action)
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->followRedirects();

        foreach (['accepted', 'pending'] as $pageName) {
            if ($pageName === $action || ('accept' === $action && 'accepted' === $pageName)) {
                continue;
            }

            $crawler = $client->request('GET', sprintf('/user/%s-submits', $pageName));

            $preActionCount = \count($crawler->filter(sprintf('form.action-%s', $action)));

            $form = $crawler
                ->filter(sprintf('form.action-%s', $action))
                ->first()
                ->form()
            ;
            $client->submit($form);

            if ('edit' === $action) {
                self::assertResponseIsSuccessful();

                return;
            }

            $crawler = $client->request('GET', sprintf('/user/%s-submits', $pageName));

            self::assertCount(--$preActionCount, $crawler->filter(sprintf('form.action-%s', $action)));
        }
    }

    /** @dataProvider provideActions */
    public function mainPageActions(string $action): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->followRedirects();
        $crawler = $client->request('GET', '/user/submits');

        $submitRepository = static::$container->get(SubmitRepository::class);

        if ('cancel' === $action) {
            $submits = $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE);
        } else {
            $submits = $submitRepository->findUserSubmitsByStatus($this->getUser(), $action);
        }

        $preActionSubmitCount = \count($submits);

        $form = $crawler
            ->filter(sprintf('form.action-%s', $action))
            ->first()
            ->form()
        ;
        $client->submit($form);

        match ($action) {
            'edit' => self::assertResponseIsSuccessful(),
            'cancel' => self::assertCount(--$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE)),
            default => self::assertCount(++$preActionSubmitCount, $submitRepository->findUserSubmitsByStatus($this->getUser(), $action))
        };
    }

    /** @dataProvider provideActions */
    public function testCsrfProtection(string $action)
    {
        if ('edit' === $action) {
            return $this->expectNotToPerformAssertions();
        }

        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());

        $submitRepository = static::$container->get(SubmitRepository::class);
        $submit = $submitRepository->findUserSubmits($this->getUser())[0];

        $client->request('POST', sprintf(
            '/user/submit-%s/%d',
            $action,
            $submit->getId(),
        ));

        self::assertResponseStatusCodeSame(403);
    }

    public function provideActions()
    {
        yield ['accept'];
        yield ['done'];
        yield ['pending'];
        yield ['reject'];
        yield ['cancel'];
        yield ['edit'];
    }

    /** @dataProvider provideButtonsText */
    public function testNavLinksWork(string $buttonText)
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());

        foreach ($this->provideRoutes() as $route) {
            $crawler = $client->request('GET', $route[0]);
            $client->click($crawler->selectLink($buttonText)->link());

            self::assertResponseIsSuccessful();
        }
    }

    public function provideButtonsText()
    {
        yield ['Back Home'];
        yield ['Talks'];
        yield ['Participations'];
        yield ['Edit Profile'];
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
