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

class SlackBlocksBuilder
{
    /** @return array<string,mixed> */
    public function buildHeader(string $text): array
    {
        return [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
                'emoji' => true,
            ],
        ];
    }

    /** @return array<string,string> */
    public function buildDivider(): array
    {
        return ['type' => 'divider'];
    }

    /** @return array<string,mixed> */
    public function buildSimpleSection(string $text): array
    {
        return [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $text,
            ],
        ];
    }

    /** @return array<string,mixed> */
    public function buildSectionWithButton(string $sectionText, string $buttonText, string $buttonValue, int $buttonActionId): array
    {
        return [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $sectionText,
            ],
            'accessory' => [
                'type' => 'button',
                'text' => [
                    'type' => 'plain_text',
                    'text' => $buttonText,
                    'emoji' => true,
                ],
                'value' => $buttonValue,
                'action_id' => (string) $buttonActionId,
            ],
        ];
    }

    /** @return array<string,mixed> */
    public function buildContext(string $text): array
    {
        return [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => $text,
                ],
            ],
        ];
    }
}
