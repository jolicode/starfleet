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

use Behat\Transliterator\Transliterator;

trait HashConferenceTrait
{
    protected function hash(string $name, string $siteUrl, \DateTimeImmutable $startDate): string
    {
        $name = Transliterator::transliterate($name);
        $siteUrl = rtrim($siteUrl, '/');

        $stringToHash = $name.$siteUrl.$startDate->format(\DateTime::ISO8601);

        return hash('md5', $stringToHash);
    }
}
