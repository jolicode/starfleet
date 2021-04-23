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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SlackRequestChecker
{
    public function __construct(
        private string $signingSecret,
    ) {
    }

    public function checkSlackRequestSanity(Request $request): ?Response
    {
        if (!$request->headers->has('X-Slack-Signature') || !$request->headers->has('X-Slack-Request-Timestamp')) {
            return new Response('Missing HTTP headers.', 400);
        }

        $timestamp = $request->headers->get('X-Slack-Request-Timestamp');
        $date = new \DateTime();

        if ($date->setTimestamp((int) $timestamp) > new \DateTime('+5 minutes')) {
            return new Response('Request is too old to be dealt with.', 408);
        }

        $signature = $request->headers->get('X-Slack-Signature');
        $version = explode('=', $signature)[0];

        $baseString = sprintf('%s:%s:%s', $version, $timestamp, $request->getContent());
        $digest = hash_hmac('sha256', $baseString, $this->signingSecret);

        if (!hash_equals(sprintf('%s=%s', $version, $digest), $signature)) {
            return new Response('Incorrect request hash.', 400);
        }

        return null;
    }
}
