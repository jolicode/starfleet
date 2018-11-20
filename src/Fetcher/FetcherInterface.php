<?php

namespace App\Fetcher;

use Psr\Http\Message\ResponseInterface;

interface FetcherInterface
{
    public function getUrl(): string;
    public function fetch(): ResponseInterface;
}
