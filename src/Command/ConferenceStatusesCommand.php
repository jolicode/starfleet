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

use App\Repository\SubmitRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConferenceStatusesCommand extends Command
{
    public function __construct(
        private SubmitRepository $submitRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('starfleet:submits:statuses');
        $this->setDescription('Updates the statuses of past submits');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->submitRepository->updateDoneSubmits();

        return Command::SUCCESS;
    }
}
