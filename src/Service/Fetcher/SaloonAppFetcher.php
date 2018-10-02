<?php

namespace App\Service\Fetcher;

use App\Service\Fetcher\FetcherInterface;
use Psr\Http\Message\ResponseInterface;

class SaloonAppFetcher implements FetcherInterface
{

    public function getUrl(): string
    {
        return 'http://saloonapp.herokuapp.com/api/v1/conferences';
    }

    public function fetch(): ResponseInterface
    {
        // TODO: Implement fetch() method.
    }
}
