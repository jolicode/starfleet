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
use App\Repository\ConferenceRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotifier
{
    public function __construct(
        private string $webHookUrl,
        private HttpClientInterface $httpClient,
        private RouterInterface $router,
        private ConferenceRepository $conferenceRepository,
        private SlackBlocksBuilder $slackBlocksBuilder
    ) {
    }

    /** @param array<Conference> $newConferences */
    public function sendDailyNotification(array $newConferences): void
    {
        $this->notify(['blocks' => $this->buildDailyBlocks($newConferences)]);
    }

    /**
     * @param array<Conference> $newConferences
     *
     * @return array<array>
     */
    public function buildDailyBlocks(array $newConferences): array
    {
        return [
            ...$this->buildConferencesBlocks($newConferences),
            ...$this->buildCfpsBlocks(),
            $this->slackBlocksBuilder->buildDivider(),
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
                $author = $submit->reduceSpeakersNames();

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
        $text .= ' : '.$submit->reduceSpeakersNames();
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
        $header = $this->slackBlocksBuilder->buildHeader('New conferences of the day');

        if (0 === \count($conferences)) {
            $conferencesBlocks = [$this->slackBlocksBuilder->buildSimpleSection('No conferences were added today !')];
        } else {
            $conferencesBlocks = [];

            foreach ($conferences as $conference) {
                if (null === $conference->getCfpUrl()) {
                    continue;
                }

                if ($conference->getExcluded()) {
                    continue;
                }

                $conferenceUrl = $this->router->generate('easyadmin', [
                    'id' => $conference->getId(),
                    'entity' => 'NextConference',
                    'action' => 'show',
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $location = $conference->isOnline() ? 'Online' : sprintf(':flag-%s:', $conference->getCountry());

                $conferenceText = sprintf('*<%s|%s>*, %s (<%s|Go to CFP>)', $conferenceUrl, $conference->getName(), $location, $conference->getCfpUrl());
                $conferencesBlocks[] = $this->slackBlocksBuilder->buildSectionWithButton($conferenceText, 'Mute this conference', 'Mute Conference', $conference->getId());
            }
        }

        return [
            $header,
            $this->slackBlocksBuilder->buildDivider(),
            ...$conferencesBlocks,
        ];
    }

    /** @return array<array> */
    private function buildCfpsBlocks(): array
    {
        $cfpsBlock = [
            $this->slackBlocksBuilder->buildHeader('Ending soon CFPs'),
            $this->slackBlocksBuilder->buildDivider(),
        ];

        $endingCfps = $this->getEndingCfps();

        if (
            0 === \count($endingCfps[0]) &&
            0 === \count($endingCfps[1]) &&
            0 === \count($endingCfps[5]) &&
            0 === \count($endingCfps[10]) &&
            0 === \count($endingCfps[20]) &&
            0 === \count($endingCfps[30])
        ) {
            $cfpsBlock[] = $this->slackBlocksBuilder->buildSimpleSection('No ending CFP to display today !');

            return $cfpsBlock;
        }

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
                $conferenceUrl = $this->router->generate('easyadmin', [
                    'id' => $conference->getId(),
                    'entity' => 'NextConference',
                    'action' => 'show',
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $location = $conference->isOnline() ? 'Online' : sprintf(':flag-%s:', $conference->getCountry());
                $conferenceText = sprintf('*<%s|%s>*, %s (<%s|Go to CFP>)', $conferenceUrl, $conference->getName(), $location, $conference->getCfpUrl());

                if ($conference->getSubmits()->count() > 0) {
                    $conferenceText .= sprintf("\n%d Talks were submitted by colleagues for %s !", \count($conference->getSubmits()), $conference->getName());
                }

                $cfpsBlock[] = $this->slackBlocksBuilder->buildSectionWithButton($conferenceText, 'Mute this conference', 'Mute Conference', $conference->getId());
            }
        }

        return $cfpsBlock;
    }

    /** @return array<int,array> */
    private function getEndingCfps(): array
    {
        $daysRemaining0 = [];
        $daysRemaining1 = [];
        $daysRemaining5 = [];
        $daysRemaining10 = [];
        $daysRemaining20 = [];
        $daysRemaining30 = [];

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $conferences = $this->conferenceRepository->findEndingCfps();

        foreach ($conferences as $conference) {
            $remainingDays = (int) ($conference->getCfpEndAt()->diff($today)->format('%a'));

            match ($remainingDays) {
                0 => $daysRemaining0[] = $conference,
                1 => $daysRemaining1[] = $conference,
                5 => $daysRemaining5[] = $conference,
                10 => $daysRemaining10[] = $conference,
                20 => $daysRemaining20[] = $conference,
                30 => $daysRemaining30[] = $conference,
                default => null,
            };
        }

        return [
            0 => $daysRemaining0,
            1 => $daysRemaining1,
            5 => $daysRemaining5,
            10 => $daysRemaining10,
            20 => $daysRemaining20,
            30 => $daysRemaining30,
        ];
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
