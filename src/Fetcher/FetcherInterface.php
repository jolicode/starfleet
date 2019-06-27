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

use App\Entity\Tag;

interface FetcherInterface
{
    public function isActive(): bool;

    public function getUrl(array $params = []): string;

    public function fetch(): array;

    public function denormalizeConferences(array $rawConferences, string $source, Tag $tag): \Generator;
}
