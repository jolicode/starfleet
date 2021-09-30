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

use App\Entity\Submit;
use App\Factory\ConferenceFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;

/**
 * @group user_account
 */
class SubmitControllerTest extends AbstractStarfleetTest
{
    /** @dataProvider provideRoutes */
    public function testAllPagesLoad(string $route)
    {
        $this->getClient()->request('GET', $route);

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
        $crawler = $this->getClient()->request('GET', '/user/submits');

        self::assertCount(1, $crawler->filter('#pending-submits-block .card'));
        self::assertCount(2, $crawler->filter('#rejected-submits-block .card'));
        self::assertCount(2, $crawler->filter('#future-submits-block .card'));

        self::assertSelectorExists('#done-submits-block a', '...Show more');
        self::assertCount(3, $crawler->filter('#done-submits-block .card'));
    }

    public function testSubmitsFormWork()
    {
        $conference = ConferenceFactory::createOne([
            'name' => 'Future Conference',
            'startAt' => new \DateTime('+10 days'),
            'endAt' => new \DateTime('+12 days'),
        ]);
        $talk = TalkFactory::createOne();
        $user = UserFactory::createOne();
        UserFactory::createOne();
        SubmitFactory::createOne([
            'users' => [$user],
            'talk' => $talk,
            'conference' => $conference,
        ]);

        $preSubmitCount = \count(SubmitFactory::all());

        $this->getClient()->request('GET', '/user/submits');
        $this->getClient()->submitForm('submit_submit', [
            'submit[conference]' => $conference->getName(),
            'submit[talk]' => $talk->getId(),
            'submit[users]' => $this->getTestUser()->getId(),
        ]);

        $allSubmits = SubmitFactory::all();

        self::assertCount($preSubmitCount + 1, $allSubmits);
        self::assertSame($this->getTestUser(), $allSubmits[$preSubmitCount]->getUsers()[0]);
    }

    /** @dataProvider provideActions */
    public function testSubmitActions(string $action)
    {
        foreach (['accepted', 'pending'] as $pageName) {
            if ($pageName === $action || ('accept' === $action && 'accepted' === $pageName)) {
                continue;
            }

            $crawler = $this->getClient()->request('GET', sprintf('/user/%s-submits', $pageName));

            $preActionCount = \count($crawler->filter(sprintf('form.action-%s', $action)));

            $this->getClient()->submitForm(ucfirst($action));

            if ('edit' === $action) {
                self::assertResponseIsSuccessful();

                return;
            }

            $crawler = $this->getClient()->request('GET', sprintf('/user/%s-submits', $pageName));

            self::assertCount(--$preActionCount, $crawler->filter(sprintf('form.action-%s', $action)));
        }
    }

    /** @dataProvider provideActions */
    public function mainPageActions(string $action): void
    {
        if ('cancel' === $action) {
            $submits = SubmitFactory::repository()->findUserSubmitsByStatus($this->getTestUser(), Submit::STATUS_DONE);
        } else {
            $submits = SubmitFactory::repository()->findUserSubmitsByStatus($this->getTestUser(), $action);
        }

        $preActionSubmitCount = \count($submits);
        $this->getClient()->request('GET', '/user/submits');
        $this->getClient()->submitForm(ucfirst($action));

        match ($action) {
            'edit' => self::assertResponseIsSuccessful(),
            'cancel' => self::assertCount(--$preActionSubmitCount, SubmitFactory::repository()->findUserSubmitsByStatus($this->getTestUser(), Submit::STATUS_DONE)),
            default => self::assertCount(++$preActionSubmitCount, SubmitFactory::repository()->findUserSubmitsByStatus($this->getTestUser(), $action))
        };
    }

    /** @dataProvider provideActions */
    public function testSubmitCsrfProtection(string $action)
    {
        if ('edit' === $action) {
            return $this->expectNotToPerformAssertions();
        }

        UserFactory::createOne();
        TalkFactory::createOne();
        ConferenceFactory::createOne();
        $submit = SubmitFactory::createOne(['users' => [$this->getTestUser()]]);

        $this->getClient()->request('POST', sprintf(
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
        foreach ($this->provideRoutes() as $route) {
            $crawler = $this->getClient()->request('GET', $route[0]);
            $this->getClient()->click($crawler->selectLink($buttonText)->link());

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

    protected function generateData()
    {
        UserFactory::createMany(2);
        ConferenceFactory::createMany(5);
        TalkFactory::createMany(5);
        SubmitFactory::createMany(2, [
            'status' => Submit::STATUS_ACCEPTED,
            'users' => [$this->getTestUser()],
        ]);
        SubmitFactory::createMany(1, [
            'status' => Submit::STATUS_PENDING,
            'users' => [$this->getTestUser()],
        ]);
        SubmitFactory::createMany(5, [
            'status' => Submit::STATUS_DONE,
            'users' => [$this->getTestUser()],
        ]);
        SubmitFactory::createMany(2, [
            'status' => Submit::STATUS_REJECTED,
            'users' => [$this->getTestUser()],
        ]);
    }
}
