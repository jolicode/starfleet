<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MapboxUrlEncoder extends AbstractExtension
{
    public function __construct(
        private string $apiToken,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mapboxEncode', [$this, 'encode']),
        ];
    }

    /** @param array<array<int>> $coordinates */
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
            'type' => 'Feature',
            'properties' => [
                'marker-color' => 'f7d325',
                'marker-size' => 's',
            ],
            'geometry' => [
                'type' => 'MultiPoint',
                'coordinates' => $coordinates,
            ],
        ];

        $encodeApiConfig = urlencode(json_encode($apiConfig));
        $encodeApiToken = http_build_query(['access_token' => $this->apiToken]);

        return sprintf('https://api.mapbox.com/styles/v1/mapbox/light-v10/static/geojson(%s)/%s/1000x400?%s', $encodeApiConfig, $zoom, $encodeApiToken);
    }
}
