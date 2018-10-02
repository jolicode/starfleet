<?php

namespace App\Service\Fetcher;

use Psr\Http\Message\ResponseInterface;

interface FetcherInterface
{
    public function getUrl(): string;
    public function fetch(): ResponseInterface;
}
