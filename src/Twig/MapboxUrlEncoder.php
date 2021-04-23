<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MapboxUrlEncoder extends AbstractExtension
{
    public function __construct(
        private string $apiToken,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('mapboxEncode', [$this, 'encode']),
        ];
    }

    /** @param array<array> $coordinates */
    public function encode(array $coordinates): ?string
    {
        if ('' === $this->apiToken) {
            return null;
        }

        $zoom = 'auto';

        if (1 === \count($coordinates)) {
            $zoom = "{$coordinates[0][0]}, {$coordinates[0][1]}, 5";
        }

        $apiConfig = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => ['marker-color' => 'f7d325'],
                    'geometry' => ['type' => 'MultiPoint', 'coordinates' => $coordinates],
                ],
            ],
        ];

        $encodeApiConfig = urlencode(json_encode($apiConfig));
        $encodeApiToken = http_build_query(['access_token' => $this->apiToken]);

        return sprintf('https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/geojson(%s)/%s/1000x600?%s', $encodeApiConfig, $zoom, $encodeApiToken);
    }
}
