<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Entity\Submit;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class UserController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private SubmitRepository $submitRepository,
    ) {
    }

    #[Route(path: '/user/account', name: 'user_account')]
    public function userAccount(ChartBuilderInterface $chartBuilder): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $attendedConferences = $this->conferenceRepository->findAttentedConferencesByUser($user);
        $pendingSubmits = range(1, 4); //$this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_PENDING);
        $acceptedSubmits = range(1, 8); //$this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_ACCEPTED);
        $rejectedSubmits = range(1, 14); //$this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_REJECTED);
        $upcomingSubmits = range(1, 1); //$this->submitRepository->findUserUpcomingUserSubmits($user);

        if (0 !== \count($pendingSubmits) + \count($acceptedSubmits) + \count($rejectedSubmits) + \count($upcomingSubmits)) {
            $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
            $chart->setData([
                'labels' => ['Pending Submits', 'Accepted Submits', 'Rejected Submits', 'Upcoming Talks'],
                'datasets' => [
                    [
                        'label' => 'Submitted talks',
                        'data' => [\count($pendingSubmits), \count($acceptedSubmits), \count($rejectedSubmits), \count($upcomingSubmits)],
                        'backgroundColor' => ['#ffc107', '#28a745', '#dc3545', '#007bff'],
                    ],
                ],
            ]);

            $chart->setOptions([
                'legend' => [
                    'position' => 'left',
                    'align' => 'start',
                ],
            ]);
        }

        return $this->render('user/account.html.twig', [
            'attendedConferences' => $attendedConferences,
            'pendingSubmits' => $pendingSubmits,
            'acceptedSubmits' => $acceptedSubmits,
            'rejectedSubmits' => $rejectedSubmits,
            'upcomingSubmits' => $upcomingSubmits,
            'chart' => $chart ?? null,
        ]);
    }
}
