<?php

namespace App\Command;

use App\Conferences\ConferencesHarvester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchConferencesCommand extends Command
{
    public function __construct(
        private ConferencesHarvester $conferencesHarvester,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('starfleet:conferences:fetch');
        $this->setDescription('Fetch conferences from Fetcher Classes');
    }

    /**
     * @param ConsoleOutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->conferencesHarvester->harvest();

        return Command::SUCCESS;
    }
}
