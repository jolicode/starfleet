<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller;

use App\Notifiers\Slack\SlackNotifier;
use App\Notifiers\Slack\SlackRequestChecker;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackController extends AbstractController
{
    private SlackNotifier $slackNotifier;
    private EntityManagerInterface $em;
    private ConferenceRepository $conferenceRepository;
    private HttpClientInterface $httpClient;
    private SlackRequestChecker $slackRequestChecker;

    public function __construct(SlackNotifier $slackNotifier, EntityManagerInterface $em, ConferenceRepository $conferenceRepository, HttpClientInterface $httpClient, SlackRequestChecker $slackRequestChecker)
    {
        $this->slackNotifier = $slackNotifier;
        $this->em = $em;
        $this->conferenceRepository = $conferenceRepository;
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->slackRequestChecker = $slackRequestChecker;
    }

    /** @Route("/slack", name="slack_endpoint", methods="POST") */
    public function slackEndPoint(Request $request): Response
    {
        if ($response = $this->slackRequestChecker->checkSlackRequestSanity($request)) {
            return $response;
        }

        $payload = json_decode($request->request->get('payload'), true);
        $actionId = $payload['actions'][0]['action_id'];
        $conference = $this->conferenceRepository->find($actionId);

        if (null === $conference) {
            return new Response('Couldn\'t find requested conference.', 404);
        }

        $conference->setExcluded(true);
        $this->em->flush();

        $dailyConferences = $this->conferenceRepository->getDailyConferences();

        $blocks = $this->slackNotifier->buildDailyBlocks($dailyConferences);

        $body = [
            'replace_original' => true,
            'blocks' => $blocks,
        ];

        $this->httpClient->request('POST', $payload['response_url'], [
            'headers' => ['Content-type' => 'application/json'],
            'json' => $body,
            'timeout' => 5,
        ]);

        return new Response('', 204);
    }
}
