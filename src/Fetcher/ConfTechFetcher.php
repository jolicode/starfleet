<?php


namespace App\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Noodlehaus\FileParser\Json;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class ConfTechFetcher implements FetcherInterface
{

    public function getUrl(): string {

        return 'https://raw.githubusercontent.com/tech-conferences/conference-data/master/conferences/2019/php.json';
    }

    public function fetch(): ResponseInterface {
        $client = new Client();

        try {
            $response = $client->request('GET', $this->getUrl());
        } catch (GuzzleException $e) {
            $e->getMessage();
        }
        return $response;
    }

    public function getLocation($conference) {

        $location = $conference->city;

        return $location;
    }

}
