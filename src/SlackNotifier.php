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

use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    private $webHookUrl;
    private $env;

    public function __construct(string $webHookUrl, HttpClientInterface $httpClient, string $env)
    {
        $this->webHookUrl = $webHookUrl;
        $this->httpClient = $httpClient;
        $this->env = $env;
    }

    public function notify(array $payload)
    {
        if ('test' !== $this->env) {
            $this->httpClient->request('POST', $this->webHookUrl, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode($payload),
            ]);
        }
    }
}
