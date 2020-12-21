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

use App\Event\CfpEndingSoonEvent;
use App\Repository\ConferenceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RemindEndingCfpCommand extends Command
{
    const REMAINING_DAYS_STEPS = [30, 20, 10, 5, 1, 0];

    private ConferenceRepository $repository;
    private $eventDispatcher;

    public function __construct(ConferenceRepository $conferenceRepository, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->repository = $conferenceRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure()
    {
        $this->setName('starfleet:conferences:remind-cfp-ending-soon');
        $this->setDescription('Trigger event for cfp ending soon');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $conferences = $this->repository->findEndingCfps();

        foreach ($conferences as $conference) {
            $remainingDays = (int) ($conference->getCfpEndAt()->diff($today)->format('%a'));

            if (\in_array($remainingDays, self::REMAINING_DAYS_STEPS, true)) {
                $this->eventDispatcher->dispatch(new CfpEndingSoonEvent($conference, $remainingDays));
            }
        }

        return 0;
    }
}
