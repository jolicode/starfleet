<?php

namespace App\Fetcher;

use Gedmo\Sluggable\Util\Urlizer;

trait FetcherTrait
{
    protected function hash(object $conference)
    {
        // Remove year so every conference is year empty
        $conference->name = preg_replace('/ 2\d{3}/', '', $conference->name);
        $startAt = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $conference->start_date);
        $conferenceYearDate = $startAt->format('Y');

        $conference->slug = Urlizer::transliterate($conference->name." $conference->tz_place"." $conferenceYearDate");
        $conference->name = $conference->name." $conferenceYearDate";
        $conference->startAtFormat = $startAt->format('Y-m-d');

        if (isset($conference->end_date)) {
            $conference->endAt = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $conference->end_date);
            $conference->endAtFormat = $conference->endAt->format('Y-m-d');
        } else {
            $conference->endAt = null;
            $conference->endAtFormat = null;
        }

        $conference->hash = hash('md5', $conference->slug.$conference->startAtFormat.$conference->endAtFormat);
    }
}
