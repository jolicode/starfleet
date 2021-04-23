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

use App\Fetcher\LocationGuesser;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateConferencesCoordinatesCommand extends Command
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private LocationGuesser $locationGuesser,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('starfleet:conferences:update-coordinates');
        $this->setDescription('Add coordinates to conferences already stored in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conferences = $this->conferenceRepository->getNullCoordinatesConferences();
        $updatedConferences = 0;

        foreach ($conferences as $conference) {
            $coordinates = $this->locationGuesser->getCoordinates($conference->getCity());
            $conference->setCoordinates($coordinates);
            ++$updatedConferences;

            $this->em->flush();
            $this->em->clear();
        }

        $output->writeln(sprintf('Updated conferences : %d', $updatedConferences));

        return Command::SUCCESS;
    }
}
