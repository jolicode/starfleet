<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Fetcher;

interface FetcherInterface
{
    public function getUrl(): string;

    public function fetch(): array;

    public function denormalizeConferences(array $rawConferences, string $source, string $tagName): \Generator;
}
