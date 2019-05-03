<?php

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
