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

    public function __construct(
        private ContinentRepository $continentRepository,
        private StatefulGeocoder $geocoder,
    ) {
    }

    public function getContinent(string $queryString): ?Continent
    {
        $countriesArray = CountriesArray::get2d('alpha2', ['continent']);
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($queryString));

        if (!$results->count()) {
            return null;
        }

        $continents = $this->getContinents();

        $country = $countriesArray[$results->first()->getCountry()->getCode()];

        return $continents[$country['continent']];
    }

    public function getCountry(string $queryString): ?string
    {
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($queryString));

        if (!$results->count()) {
            return null;
        }

        return strtoupper($results->first()->getCountry()->getCode());
    }

    /** @return array<float> */
    public function getCoordinates(string $queryString): ?array
    {
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($queryString));

        if (!$results->count()) {
            return null;
        }

        return $results->first()->getCoordinates()->toArray();
    }

    /** @return array<Continent> */
    private function getContinents(): array
    {
        return $this->continents ??= $this->continentRepository->findAllAsKey();
    }
}
