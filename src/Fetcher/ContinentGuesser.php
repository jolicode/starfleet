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
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle6\Client;
use SameerShelavale\PhpCountriesArray\CountriesArray;

class ContinentGuesser
{
    private $continents;

    public function __construct(ContinentRepository $continentRepository)
    {
        $this->continents = $continentRepository->findAllAsKey();
    }

    public function getContinent(string $queryString): ?Continent
    {
        $provider = new Nominatim(new Client(), 'https://nominatim.openstreetmap.org/', 'Fetch some geoloc thx OSM');
        $geocoder = new StatefulGeocoder($provider);
        $countriesArray = CountriesArray::get2d('alpha2', ['continent']);
        $results = $geocoder->geocodeQuery(GeocodeQuery::create($queryString));
        $continent = $this->continents[$countriesArray[$results->first()->getCountry()->getCode()]['continent']];

        if (!$results->count()) {
            return null;
        }

        return $continent;
    }
}
