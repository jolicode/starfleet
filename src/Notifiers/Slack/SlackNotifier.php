<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Notifiers\Slack;

use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotifier
{
    private LoggerInterface $logger;

    public function __construct(
        private string $webHookUrl,
        private HttpClientInterface $httpClient,
        private RouterInterface $router,
        private SlackBlocksBuilder $slackBlocksBuilder,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param array<Conference>            $newConferences
     * @param array<int,array<Conference>> $endingCfps
     * */
    public function sendDailyNotification(array $newConferences, array $endingCfps): void
    {
        if (0 === \count($newConferences) && 0 === \count($endingCfps)) {
            $this->logger->info('No new conferences or ending CFPs today, not sending notification.');

            return;
        }

        $this->notify(['blocks' => $this->buildDailyBlocks($newConferences, $endingCfps)]);
    }

    /**
     * @param array<Conference>            $newConferences
     * @param array<int,array<Conference>> $endingCfps
     *
     * @return array<array>
     */
    public function buildDailyBlocks(array $newConferences, array $endingCfps): array
    {
        return [
            ...$this->buildConferencesBlocks($newConferences),
            ...$this->buildCfpsBlocks($endingCfps),
        ];
    }

    /** @param array<Submit> $submits */
    public function sendNewTalkSubmittedNotification(array $submits, Talk $talk): void
    {
        $blocks = [
            'blocks' => [
                $this->slackBlocksBuilder->buildHeader('🗣  New submitted talk'),
                $this->slackBlocksBuilder->buildDivider(),
                $this->slackBlocksBuilder->buildSimpleSection(sprintf('*Title :* %s', $talk->getTitle())),
                $this->slackBlocksBuilder->buildSimpleSection(sprintf('*Introduction :* %s', $talk->getIntro())),
            ],
        ];

        if ($submits) {
            $submittedText = '*Submitted for :*';

            foreach ($submits as $k => $submit) {
                $conference = sprintf('<%s|%s>', $submit->getConference()->getSiteUrl(), $submit->getConference()->getName());
                $status = Submit::STATUS_EMOJIS[$submit->getStatus()];
                $author = $submit->getSpeakersNames();

                if (0 === $k) {
                    $submittedText .= sprintf(' %s (%s) by %s', $conference, $status, $author);
                } else {
                    $submittedText .= sprintf(', %s (%s) by %s', $conference, $status, $author);
                }

                ++$k;
            }

            $blocks['blocks'][] = $this->slackBlocksBuilder->buildSimpleSection($submittedText);
        }

        $talkUrl = $this->router->generate('easyadmin', [
            'id' => $talk->getId(),
            'entity' => 'Talk',
            'action' => 'show',
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $blocks['blocks'][] = $this->slackBlocksBuilder->buildDivider();
        $blocks['blocks'][] = $this->slackBlocksBuilder->buildContext(sprintf('<%s|See the talk on Starfleet>', $talkUrl));

        $this->notify($blocks);
    }

    public function sendSubmitStatusChangedNotification(Submit $submit): void
    {
        if (Submit::STATUS_ACCEPTED === $submit->getStatus()) {
            $header = $this->slackBlocksBuilder->buildHeader('🎉  Talk accepted');
        } else {
            $header = $this->slackBlocksBuilder->buildHeader('😢  Talk rejected');
        }

        $text = \count($submit->getUsers()) > 1 ? '*Speakers*' : '*Speaker*';
        $text .= ' : '.$submit->getSpeakersNames();
        $text .= ' *Conference* : ';
        $text .= sprintf('<%s|%s>', $submit->getConference()->getSiteUrl(), $submit->getConference()->getName());

        $talkUrl = $this->router->generate('easyadmin', [
            'id' => $submit->getTalk()->getId(),
            'entity' => 'Talk',
            'action' => 'show',
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $blocks = [
            'blocks' => [
                $header,
                $this->slackBlocksBuilder->buildDivider(),
                $this->slackBlocksBuilder->buildSimpleSection(sprintf('*Title :* %s', $submit->getTalk()->getTitle())),
                $this->slackBlocksBuilder->buildSimpleSection($text),
                $this->slackBlocksBuilder->buildDivider(),
                $this->slackBlocksBuilder->buildContext(sprintf('<%s|See the talk on Starfleet>', $talkUrl)),
            ],
        ];

        $this->notify($blocks);
    }

    /**
     * @param array<Conference> $conferences
     *
     * @return array<array>
     */
    private function buildConferencesBlocks(array $conferences): array
    {
        if (0 === \count($conferences)) {
            return [];
        }

        $header = $this->slackBlocksBuilder->buildHeader('New conferences of the day');

        $conferencesBlocks = [];

        foreach ($conferences as $conference) {
            if ($conference->getExcluded()) {
                continue;
            }

            $location = $conference->isOnline() ? 'Online' : sprintf(':flag-%s:', $conference->getCountry());
            $conferenceUrl = $this->router->generate('conferences_show', [
                'slug' => $conference->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            if ($conference->getCfpUrl()) {
                $conferenceText = sprintf('*<%s|%s>*, %s (<%s|Go to CFP>)', $conferenceUrl, $conference->getName(), $location, $conference->getCfpUrl());
            } else {
                $conferenceText = sprintf('*<%s|%s>*, %s (No CFP page)', $conferenceUrl, $conference->getName(), $location);
            }

            $conferencesBlocks[] = $this->slackBlocksBuilder->buildSectionWithButton($conferenceText, 'Mute this conference', 'Mute Conference', $conference->getId());
        }

        return [
            $header,
            $this->slackBlocksBuilder->buildDivider(),
            ...$conferencesBlocks,
        ];
    }

    /**
     * @param array<int,array<Conference>> $endingCfps
     *
     * @return array<array>
     * */
    private function buildCfpsBlocks(array $endingCfps): array
    {
        if (0 === \count($endingCfps)) {
            return [];
        }

        $cfpsBlock = [
            $this->slackBlocksBuilder->buildHeader('Ending soon CFPs'),
            $this->slackBlocksBuilder->buildDivider(),
        ];

        foreach ($endingCfps as $remainingDays => $conferences) {
            if (0 === \count($conferences)) {
                continue;
            }

            if (!$remainingDays) {
                $cfpText = '* > CFPs ending today !*';
            } elseif (1 === $remainingDays) {
                $cfpText = '* > CFPs ending tomorrow*';
            } else {
                $cfpText = sprintf('* > CFPs with %d days remaining*', $remainingDays);
            }

            $cfpsBlock[] = $this->slackBlocksBuilder->buildSimpleSection($cfpText);

            foreach ($conferences as $conference) {
                $conferenceUrl = $this->router->generate('conferences_show', [
                    'slug' => $conference->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $location = $conference->isOnline() ? 'Online' : sprintf(':flag-%s:', $conference->getCountry());

                if ($conference->getCfpUrl()) {
                    $conferenceText = sprintf('*<%s|%s>*, %s (<%s|Go to CFP>)', $conferenceUrl, $conference->getName(), $location, $conference->getCfpUrl());
                } else {
                    $conferenceText = sprintf('*<%s|%s>*, %s (No CFP page)', $conferenceUrl, $conference->getName(), $location);
                }

                if ($conference->getSubmits()->count() > 0) {
                    $conferenceText .= sprintf("\n%d Talks were submitted by colleagues for %s !", \count($conference->getSubmits()), $conference->getName());
                }

                $cfpsBlock[] = $this->slackBlocksBuilder->buildSectionWithButton($conferenceText, 'Mute this conference', 'Mute Conference', $conference->getId());
            }
        }

        return $cfpsBlock;
    }

    /** @param array<mixed> $blocks */
    private function notify(array $blocks): void
    {
        $this->httpClient->request('POST', $this->webHookUrl, [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($blocks),
        ]);
    }
}
