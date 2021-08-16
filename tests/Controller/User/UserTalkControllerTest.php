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

use App\Entity\User;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use App\Repository\TalkRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTalkControllerTest extends WebTestCase
{
    private ?User $user = null;

    /** @dataProvider provideRoutes */
    public function testPagesLoad(string $route)
    {
        $client = $this->createClient();
        $client->loginUser($this->getUser());
        $client->request('GET', $route);

        self::assertResponseIsSuccessful();
    }

    public function provideRoutes(): iterable
    {
        yield ['/user/talks'];
        yield ['/user/talks/all'];
    }

    public function testTalksPageWork()
    {
        $client = $this->createClient();
        $talkRepository = static::$container->get(TalkRepository::class);

        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', '/user/talks');

        $talks = $talkRepository->findUserTalks($this->getUser());

        if (\count($talks) < 4) {
            self::assertCount(\count($talks), $crawler->filter('#all-talks-block .card'));
        } else {
            self::assertSelectorExists('#all-talks-block a', '...Show more');
            self::assertCount(3, $crawler->filter('#all-talks-block .card'));
        }
    }

    public function testTalksFormWork()
    {
        $client = $this->createClient();
        $talkRepository = static::$container->get(TalkRepository::class);

        $client->loginUser($this->getUser());
        $client->request('GET', '/user/talks');

        $talks = $talkRepository->findUserTalks($this->getUser());
        $preFormTalksCount = \count($talks);

        $conferenceRepository = static::$container->get(ConferenceRepository::class);

        $client->submitForm('submit_talk', [
            'new_talk[title]' => 'My Amazing Test Title',
            'new_talk[intro]' => 'Bewildering intro',
            'new_talk[conference]' => $conferenceRepository->find(1)->getName(),
            'new_talk[users]' => [$this->getUser()->getId()],
        ]);

        self::assertCount(++$preFormTalksCount, $talkRepository->findUserTalks($this->getUser()));
    }

    public function testEditTalkPageWork()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());

        $submitRepository = static::$container->get(SubmitRepository::class);
        $submit = $submitRepository->findUserSubmits($this->getUser())[0];
        $talk = $submit->getTalk();

        $client->request('GET', sprintf('/user/talk-edit/%d', $talk->getId()));
        self::assertResponseIsSuccessful();

        $talk->setTitle('My old boring title');
        $talk->setIntro('My old inaccurate intro');

        $client->submitForm('edit_talk', [
            'edit_talk[title]' => 'My new incredible title',
            'edit_talk[intro]' => 'My new stunning intro',
        ]);

        $talkRepository = static::$container->get(TalkRepository::class);
        $talk = $talkRepository->find($talk->getId());

        self::assertSame('My new incredible title', $talk->getTitle());
        self::assertSame('My new stunning intro', $talk->getIntro());
    }

    /** @dataProvider provideRoutes */
    public function testAllEditTalkLinksWork(string $route)
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->loginUser($this->getUser());
        $crawler = $client->request('GET', $route);

        $form = $crawler
            ->filter('form.action-edit')
            ->first()
            ->form()
        ;
        $client->submit($form);

        self::assertResponseIsSuccessful();
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
        yield ['Submits'];
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
