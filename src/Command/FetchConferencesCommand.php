<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Command;

use App\Fetcher\ConfTechFetcher;
use App\Fetcher\FetcherInterface;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchConferencesCommand extends Command
{
    const SALOON_URL = 'http://saloonapp.herokuapp.com/api/v1/conferences?tags=';
    private $em;
    private $messageFactory;
    private $client;
    private $fetcher;
    private $appFetchers;

    public function __construct(iterable $appFetchers, RegistryInterface $doctrine, MessageFactory $messageFactory, HttpClient $client, ConfTechFetcher $fetcher)
    {
        $this->em = $doctrine->getManager();
        $this->messageFactory = $messageFactory;
        $this->client = $client;
        $this->fetcher = $fetcher;
        $this->appFetchers = $appFetchers;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet-conferences-fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fetchers = $this->appFetchers;

        $newConferencesCount = 0;

        foreach ($fetchers as $fetcher) {
            /** @var FetcherInterface $fetcher */
            $f = $fetcher->fetch();
            $newConferencesCount += $f['newConferencesCount'];
        }

        $this->em->flush();

        $output->writeln('You add '.($newConferencesCount).' conference(s)');
    }
}
