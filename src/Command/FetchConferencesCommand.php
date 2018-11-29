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

use App\Entity\Tag;
use App\Fetcher\ConfTechFetcher;
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

    public function __construct(RegistryInterface $doctrine, MessageFactory $messageFactory, HttpClient $client, ConfTechFetcher $fetcher)
    {
        $this->em = $doctrine->getManager();
        $this->messageFactory = $messageFactory;
        $this->client = $client;
        $this->fetcher = $fetcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('starfleet-conferences-fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = $this->em->getRepository(Tag::class)->findAll();
        $tagsList = implode(',', array_map(function ($tag) { return $tag->getName(); }, $tags));

        $response = $this->fetcher->fetch();

        $fetchedConferences = (array) json_decode($response->getBody());

        $this->em->flush();
    }
}
