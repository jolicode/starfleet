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

use Http\Client\HttpClient;
use Http\Message\MessageFactory;

class SlackNotifier
{
    const EMPTY_PAYLOAD = [
        'attachments' => [],
    ];
    const LONG_FIELD = [
        'title' => '',
        'value' => '',
        'short' => false,
    ];
    const SHORT_FIELD = [
        'title' => '',
        'value' => '',
        'short' => true,
    ];
    const ATTACHMENT = [
        'pretext' => '',
        'text' => '',
        'color' => '#0ab086',
        'fallback' => 'Announce',
        'mrkdwn_in' => ['text', 'pretext', 'fields'],
        'fields' => [],
    ];

    private $httpClient;
    private $messageFactory;
    private $webHookUrl;

    public function __construct(HttpClient $httpClient, MessageFactory $messageFactory, string $webHookUrl)
    {
        $this->httpClient = $httpClient;
        $this->messageFactory = $messageFactory;
        $this->webHookUrl = $webHookUrl;
    }

    public function notify(array $payload)
    {
        $this->httpClient->sendRequest(
            $this->messageFactory->createRequest('POST', $this->webHookUrl, [
                'Content-type' => 'application/json',
            ], json_encode($payload))
        );
    }
}
