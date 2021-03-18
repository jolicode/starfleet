<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App;

use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotifier
{
    private const EMPTY_PAYLOAD = [
        'attachments' => [],
    ];
    private const LONG_FIELD = [
        'title' => '',
        'value' => '',
        'short' => false,
    ];
    private const SHORT_FIELD = [
        'title' => '',
        'value' => '',
        'short' => true,
    ];
    private const ATTACHMENT = [
        'pretext' => '',
        'text' => '',
        'color' => '#0ab086',
        'fallback' => 'Announce',
        'mrkdwn_in' => ['text', 'pretext', 'fields'],
        'fields' => [],
    ];

    private string $webHookUrl;
    private string $env;
    private HttpClientInterface $httpClient;
    private RouterInterface $router;

    public function __construct(string $webHookUrl, string $env, HttpClientInterface $httpClient, RouterInterface $router)
    {
        $this->webHookUrl = $webHookUrl;
        $this->env = $env;
        $this->httpClient = $httpClient;
        $this->router = $router;
    }

    /**  @param array<Conference> $newConferences */
    public function sendNewConferencesNotification(array $newConferences): void
    {
        if (1 === \count($newConferences)) {
            $attachment = $this->buildSingleConferenceAttachment($newConferences[0]);
        } else {
            $attachment = $this->buildMultipleConferencesAttachment($newConferences);
        }

        $this->notify([$attachment]);
    }

    /** @param array<Submit> $submits */
    public function sendNewTalkSubmittedNotification(array $submits, Talk $talk): void
    {
        $talkAttachment = self::ATTACHMENT;

        $talkAttachment['pretext'] = '🗣  *New submitted talk*';
        $talkAttachment['title'] = $talk->getTitle();
        $talkAttachment['text'] = $talk->getIntro();

        if (0 < \count($submits)) {
            $submitsAttachment = self::ATTACHMENT;
            $submitsAttachment['title'] = 'Submitted at : ';

            foreach ($submits as $submit) {
                $conferenceField = self::LONG_FIELD;
                $conference = sprintf('<%s|%s>', $submit->getConference()->getSiteUrl(), $submit->getConference()->getName());
                $status = Submit::STATUS_EMOJIS[$submit->getStatus()];
                $author = $submit->reduceSpeakersNames();

                $conferenceField['value'] = sprintf('%s (%s) by %s', $conference, $status, $author);
                $submitsAttachment['fields'][] = $conferenceField;
            }

            $this->notify([$talkAttachment, $submitsAttachment]);

            return;
        }

        $this->notify([$talkAttachment]);
    }

    public function sendSubmitStatusChangedNotification(Submit $submit): void
    {
        $submitAttachment = self::ATTACHMENT;

        if (Submit::STATUS_ACCEPTED === $submit->getStatus()) {
            $submitAttachment['pretext'] = '🎉  *Talk accepted*';
        } elseif (Submit::STATUS_REJECTED === $submit->getStatus()) {
            $submitAttachment['pretext'] = '😢  *Talk rejected*';
        }

        $submitAttachment['title'] = $submit->getTalk()->getTitle();
        $submitAttachment['text'] = $submit->getTalk()->getIntro();

        $speakersField = self::LONG_FIELD;
        $speakersField['title'] = \count($submit->getUsers()) > 1 ? 'Speakers' : 'Speaker';
        $speakersField['value'] = $submit->reduceSpeakersNames();
        $submitAttachment['fields'][] = $speakersField;

        $conferenceField = self::SHORT_FIELD;
        $conferenceField['title'] = 'Conference';
        $conferenceField['value'] = sprintf('<%s|%s>', $submit->getConference()->getSiteUrl(), $submit->getConference()->getName());
        $submitAttachment['fields'][] = $conferenceField;

        $this->notify([$submitAttachment]);
    }

    public function sendCfPEndingSoonNotification(Conference $conference, int $remainingDays): void
    {
        $cfpAttachment = self::ATTACHMENT;
        $template = '🔊  CFP for %s (%s) is closing %s';
        if (null !== $conference->getSiteUrl()) {
            $conferenceLink = sprintf('<%s|%s>', $conference->getSiteUrl(), $conference->getName());
        } else {
            $conferenceLink = $conference->getName();
        }
        $countdown = sprintf('in *%d day%s* !', $remainingDays, $remainingDays > 1 ? 's' : '');

        switch ($remainingDays) {
            case 30:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), $countdown.' 😀');
                break;
            case 20:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), $countdown.' 🙂');
                break;
            case 10:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), $countdown.' 😮');
                break;
            case 5:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), $countdown.' 😨');
                break;
            case 1:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), $countdown.' 😰');
                break;
            case 0:
                $cfpAttachment['pretext'] = sprintf($template, $conferenceLink, $conference->getCity(), '*today* ! 😱');
                break;
        }

        if ($conference->getSubmits()->count() > 0) {
            $talksField = self::SHORT_FIELD;
            $talksField['title'] = 'Talks submitted by colleagues';
            $talksField['value'] = $conference->getSubmits()->count().'  📝';
            $cfpAttachment['fields'][] = $talksField;
        }

        $actionsField = self::SHORT_FIELD;
        $actionsField['title'] = 'Submit a talk';
        $actionsField['value'] = sprintf('<%s|%s>', $conference->getCfpUrl(), 'Go to the CFP  👉');
        $cfpAttachment['fields'][] = $actionsField;

        $this->notify([$cfpAttachment]);
    }

    /** @param array<mixed> $attachments */
    private function notify(array $attachments): void
    {
        $payload = self::EMPTY_PAYLOAD;
        $payload['attachments'] = $attachments;

        if ('test' !== $this->env) {
            $this->httpClient->request('POST', $this->webHookUrl, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode($payload),
            ]);
        }
    }

    /** @param array<Conference> $conferences
     *  @return array<mixed>
     */
    private function buildMultipleConferencesAttachment(array $conferences): array
    {
        $conferencesAttachment = self::ATTACHMENT;
        $conferencesAttachment['pretext'] = sprintf('✨  %d new conferences', \count($conferences));

        foreach ($conferences as $conference) {
            $conferenceField = self::LONG_FIELD;

            if (null !== $conference->getStartAt() && null !== $conference->getEndAt()) {
                $startDate = $conference->getStartAt()->format('d F Y');
                $endDate = $conference->getEndAt()->format('d F Y');

                if (null !== $conference->getCountry() && !$conference->isOnline()) {
                    $conferenceField['title'] = sprintf('From %s to %s at %s (%s)', $startDate, $endDate, $conference->getCity(), $conference->getCountry());
                } elseif ($conference->isOnline()) {
                    $conferenceField['title'] = sprintf('From %s to %s Online', $startDate, $endDate);
                } else {
                    $conferenceField['title'] = sprintf('From %s to %s at %s', $startDate, $endDate, $conference->getCity());
                }
            }

            $starfleetLink = $this->router->generate('conferences_show', [
                'slug' => $conference->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $conferenceField['value'] = sprintf('<%s|%s>', $starfleetLink, $conference->getName());

            if (null !== $conference->getCfpUrl()) {
                $conferenceField['value'] .= sprintf(' - <%s|CFP>', $conference->getCfpUrl());

                if (null !== $conference->getCfpEndAt()) {
                    $conferenceField['value'] .= sprintf(' (CfP ends on %s ⏱)', $conference->getCfpEndAt()->format('d F Y'));
                }
            }
            $conferencesAttachment['fields'][] = $conferenceField;
        }

        return $conferencesAttachment;
    }

    /** @return array<mixed> */
    private function buildSingleConferenceAttachment(Conference $conference): array
    {
        $conferenceAttachment = self::ATTACHMENT;
        $conferenceAttachment['pretext'] = '✨  *New conference added*';
        $conferenceAttachment['title'] = $conference->getName();
        $conferenceAttachment['title_link'] = $conference->getSiteUrl();

        if (null !== $conference->getCfpUrl()) {
            $cfpField = self::LONG_FIELD;
            if (null !== $conference->getCfpEndAt()) {
                $cfpField['title'] = sprintf('CFP open until %s', $conference->getCfpEndAt()->format('d F Y'));
            } else {
                $cfpField['title'] = 'CFP';
            }
            $cfpField['value'] = sprintf('<%s|Submit a talk> 👉', $conference->getCfpUrl());

            $conferenceAttachment['fields'][] = $cfpField;
        }

        $startDateField = self::SHORT_FIELD;
        $startDateField['title'] = 'From  🕑';
        $startDateField['value'] = $conference->getStartAt()->format('d F Y');
        $conferenceAttachment['fields'][] = $startDateField;

        $endDateField = self::SHORT_FIELD;
        $endDateField['title'] = 'To  🕣';
        if (null !== $conference->getEndAt()) {
            $endDateField['value'] = $conference->getEndAt()->format('d F Y');
        } else {
            $endDateField['value'] = 'Unknown';
        }
        $conferenceAttachment['fields'][] = $endDateField;

        if ($conference->isOnline()) {
            $cityField = self::SHORT_FIELD;
            $cityField['title'] = 'Online Conference 🖥️';
            $cityField['value'] = '127.0.0.1';
            $conferenceAttachment['fields'][] = $cityField;
        } else {
            $cityField = self::SHORT_FIELD;
            $cityField['title'] = 'City  🏙️';
            $cityField['value'] = $conference->getCity();
            $conferenceAttachment['fields'][] = $cityField;

            $countryField = self::SHORT_FIELD;
            $countryField['title'] = 'Country  🗺';
            $countryField['value'] = $conference->getCountry();
            $conferenceAttachment['fields'][] = $countryField;
        }

        if ($conference->getParticipations()->count() > 0) {
            $starfleetLinkField = self::SHORT_FIELD;
            $starfleetLinkField['title'] = 'Starfleet link  🚀';
            $starfleetLinkField['value'] = $this->router->generate('conferences_show', [
                'slug' => $conference->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $conferenceAttachment['fields'][] = $starfleetLinkField;
        }

        return $conferenceAttachment;
    }
}
