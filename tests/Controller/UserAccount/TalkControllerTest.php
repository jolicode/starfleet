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

use App\Factory\ConferenceFactory;
use App\Factory\SubmitFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use App\Tests\AbstractStarfleetTest;

/**
 * @group user_account
 */
class TalkControllerTest extends AbstractStarfleetTest
{
    /** @dataProvider provideRoutes */
    public function testPagesLoad(string $route)
    {
        $this->getClient()->request('GET', $route);
        self::assertResponseIsSuccessful();
    }

    public function provideRoutes(): iterable
    {
        yield ['/user/talks'];
        yield ['/user/talks/all'];
    }

    public function testTalksPageWork()
    {
        $crawler = $this->getClient()->request('GET', '/user/talks');

        self::assertSelectorExists('#all-talks-block a', '...Show more');
        self::assertCount(3, $crawler->filter('#all-talks-block .card'));
    }

    public function testTalksFormWork()
    {
        $conference = ConferenceFactory::createOne([
            'name' => 'Future Conference',
            'startAt' => new \DateTime('+10 days'),
            'endAt' => new \DateTime('+12 days'),
        ]);

        $preSubmitCount = \count(TalkFactory::all());

        $this->getClient()->request('GET', '/user/talks');
        $this->getClient()->submitForm('submit_talk', [
            'new_talk[title]' => 'My Amazing Test Title',
            'new_talk[intro]' => 'Bewildering intro',
            'new_talk[conference]' => $conference->getName(),
            'new_talk[users]' => $this->getTestUser()->getId(),
        ]);

        self::assertCount($preSubmitCount + 1, TalkFactory::all());
    }

    public function testEditTalkPageWork()
    {
        $talk = TalkFactory::createOne([
            'title' => 'Old Title',
            'intro' => 'Old Intro',
        ]);
        UserFactory::createOne();
        SubmitFactory::createOne([
            'users' => [$this->getTestUser()],
            'talk' => $talk,
            'conference' => ConferenceFactory::createOne(),
        ]);

        $this->getClient()->request('GET', sprintf('/user/talk-edit/%d', $talk->getId()));
        $this->getClient()->submitForm('edit_talk', [
            'edit_talk[title]' => 'My new incredible title',
            'edit_talk[intro]' => 'My new stunning intro',
        ]);

        self::assertSame('My new incredible title', $talk->getTitle());
        self::assertSame('My new stunning intro', $talk->getIntro());
    }

    /** @dataProvider provideRoutes */
    public function testAllEditTalkLinksWork(string $route)
    {
        $this->getClient()->request('GET', $route);
        $this->getClient()->submitForm('Edit');

        self::assertResponseIsSuccessful();
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
        yield ['Participations'];
        yield ['Edit Profile'];
    }

    protected function generateData()
    {
        UserFactory::createMany(2);
        ConferenceFactory::createMany(5);

        foreach (TalkFactory::createMany(4) as $talk) {
            SubmitFactory::createOne([
                'users' => [$this->getTestUser()],
                'talk' => $talk,
            ]);
        }
    }
}
