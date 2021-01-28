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

use App\Entity\Continent;
use App\Repository\ContinentRepository;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use SameerShelavale\PhpCountriesArray\CountriesArray;

class LocationGuesser
{
    /** @var array<Continent> */
    private array $continents;
    private StatefulGeocoder $geocoder;

    public function __construct(ContinentRepository $continentRepository, StatefulGeocoder $geocoder)
    {
        $this->continents = $continentRepository->findAllAsKey();
        $this->geocoder = $geocoder;
    }

    public function getContinent(string $queryString): ?Continent
    {
        $countriesArray = CountriesArray::get2d('alpha2', ['continent']);
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($queryString));

        if (!$results->count()) {
            return null;
        }

        return $this->continents[$countriesArray[$results->first()->getCountry()->getCode()]['continent']];
    }

    public function getCountry(string $queryString): ?string
    {
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($queryString));

        if (!$results->count()) {
            return null;
        }

        return $results->first()->getCountry()->getCode();
    }
}
