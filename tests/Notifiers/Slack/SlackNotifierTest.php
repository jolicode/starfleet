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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotifierTest extends WebTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideDailyData
     */
    public function testBuildDailyBlocks(array $newConferences, array $endingCfps, int $expectedSections)
    {
        $client = new MockHttpClient();

        $slackNotifier = $this->createSlackNotifier($client);
        $dailyBlocks = $slackNotifier->buildDailyBlocks($newConferences, $endingCfps);
        $slackNotifier->sendDailyNotification($newConferences, $endingCfps);

        $sections = [];

        foreach ($dailyBlocks as $block) {
            if ('section' === $block['type']) {
                $sections[] = $block;
            }
        }

        self::assertCount($expectedSections, $sections);

        if (!$newConferences && !$endingCfps) {
            self::assertSame(0, $client->getRequestsCount());
        } else {
            self::assertSame(1, $client->getRequestsCount());
        }
    }

    public function provideDailyData()
    {
        yield 'Test 0 new conferences and 0 ending Cfps' => [
            'newConferences' => [],
            'endingCfps' => [],
            // There shouldn't be a notfication
            'expectedSections' => 0,
        ];

        $testConference = $this->createConference(new \DateTime());

        yield 'Test 2 conferences and 2 ending Cfps' => [
            'newConferences' => [$testConference, $testConference],
            'endingCfps' => [
                0 => [$testConference, $testConference],
            ],
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
            'endingCfps' => [
                0 => [$cfp0days],
                1 => [$cfp1days],
                5 => [$cfp5days],
                10 => [$cfp10days],
                20 => [$cfp20days],
                30 => [$cfp30days],
            ],
            // There should be one for each CFP milestone + one for corresponding CFP.
            'expectedSections' => 12,
        ];

        $excludedConference = $this->createConference(new \DateTime(), true);

        yield 'Test excluded conference is not sending notifications' => [
            'newConferences' => [$testConference, $excludedConference],
            'endingCfps' => [],
            // One for $testConference.
            'expectedSections' => 1,
        ];
    }

    private function createSlackNotifier(HttpClientInterface $client): SlackNotifier
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

        return new SlackNotifier(
            $webHookUrl,
            $client,
            $router->reveal(),
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
