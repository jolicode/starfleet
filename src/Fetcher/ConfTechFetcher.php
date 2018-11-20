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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ConfTechFetcher implements FetcherInterface
{
    const SOURCE_CONFS_TECH = 'conf-tech';

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getUrl(): string
    {
        return 'https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/2019/php.json';
    }

    public function fetch(): ResponseInterface
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $this->getUrl());
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
        }

        return $response;
    }

    public function getLocation($conference)
    {
        $location = $conference->city;

        return $location;
    }
}
