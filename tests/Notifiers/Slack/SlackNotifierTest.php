<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Notifiers\Slack;

use App\Entity\Conference;
use App\Notifiers\Slack\SlackBlocksBuilder;
use App\Notifiers\Slack\SlackNotifier;
use App\Repository\ConferenceRepository;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotifierTest extends WebTestCase
{
    /**
     * @dataProvider provideDailyData
     */
    public function testBuildDailyBlocks(array $newConferences, array $endingCfps, int $expectedSections)
    {
        $client = new MockHttpClient();

        $slackNotifier = $this->createSlackNotifier($endingCfps, $client);
        $dailyBlocks = $slackNotifier->buildDailyBlocks($newConferences);
        $slackNotifier->sendDailyNotification($newConferences);

        $sections = [];

        foreach ($dailyBlocks as $block) {
            if ('section' === $block['type']) {
                $sections[] = $block;
            }
        }

        self::assertCount($expectedSections, $sections);
        self::assertSame(1, $client->getRequestsCount());
    }

    public function provideDailyData()
    {
        yield 'Test 0 new conferences and 0 ending Cfps' => [
            'newConferences' => [],
            'endingCfps' => [],
            // One should say no conference to display, the other no CFP to display.
            'expectedSections' => 2,
        ];

        $pastConference = $this->createConference(new \DateTime('-1 year'));
        $cfpNotMatchingConference = $this->createConference(new \DateTime('+2 days'));
        $cfpMatchingConference = $this->createConference(new \DateTime('+5 days'));

        // CFP milestones represent the CFP remaining days we are interested in. They should send notifications.
        yield 'Test conferences not matching CFP milestones dont send notifications' => [
            'newConferences' => [],
            'endingCfps' => [$pastConference, $cfpNotMatchingConference, $cfpMatchingConference],
            // There should be one telling no conferences to display, one for the CFP milestone which is 5, and one for the actual ending CFP.
            'expectedSections' => 3,
        ];

        $testConference = $this->createConference(new \DateTime());

        yield 'Test 2 conferences and 2 ending Cfps' => [
            'newConferences' => [$testConference, $testConference],
            'endingCfps' => [$testConference, $testConference],
            // There should be one for each new conference and CFP, and one for the CFP milestone.
            'expectedSections' => 5,
        ];

        $cfp0days = $this->createConference(new \DateTime());
        $cfp1days = $this->createConference(new \DateTime('+1 day'));
        $cfp5days = $this->createConference(new \DateTime('+5 days'));
        $cfp10days = $this->createConference(new \DateTime('+10 days'));
        $cfp20days = $this->createConference(new \DateTime('+20 days'));
        $cfp30days = $this->createConference(new \DateTime('+30 days'));

        yield 'Test all CFP end date milestones work' => [
            'newConferences' => [],
            'endingCfps' => [$cfp0days, $cfp1days, $cfp5days, $cfp10days, $cfp20days, $cfp30days],
            // There should be one telling no conferences to display, and one for each CFP milestone + one for corresponding CFP.
            'expectedSections' => 13,
        ];

        $excludedConference = $this->createConference(new \DateTime(), true);

        yield 'Test excluded conference is not sending notifications' => [
            'newConferences' => [$testConference, $excludedConference],
            'endingCfps' => [],
            // One for $testConference, one to say no ending CFP.
            'expectedSections' => 2,
        ];
    }

    private function createSlackNotifier(array $endingCfps, HttpClientInterface $client): SlackNotifier
    {
        $webHookUrl = 'https://Slack Webhook';
        $slackBlocksBuilder = new SlackBlocksBuilder();

        $router = $this->prophesize(Router::class);
        $router
            ->generate(
                Argument::type('string'),
                ['slug' => 'test-slug'],
                Argument::type('integer')
            )
            ->willReturn('Route URL');

        $conferenceRepository = $this->prophesize(ConferenceRepository::class);
        $conferenceRepository
            ->findEndingCfps()
            ->willReturn($endingCfps);

        return new SlackNotifier(
            $webHookUrl,
            $client,
            $router->reveal(),
            $conferenceRepository->reveal(),
            $slackBlocksBuilder,
        );
    }

    private function createConference(\DateTimeInterface $cfpEndAt, bool $excluded = false)
    {
        $conference = new Conference();
        $conference->setName('Test Conference');
        $conference->setCfpEndAt($cfpEndAt);
        $conference->setCfpUrl('https://starfleet.jolicode.com');
        $conference->setOnline(false);
        $conference->setExcluded($excluded);
        $conference->setCountry('Test Country');
        $conference->setSlug('test-slug');

        $conferenceReflection = new \ReflectionClass(Conference::class);
        $conferenceId = $conferenceReflection->getProperty('id');
        $conferenceId->setAccessible(true);
        $conferenceId->setValue($conference, 1);

        return $conference;
    }
}
