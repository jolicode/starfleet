<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller\UserAccount;

use App\Entity\Submit;
use App\Repository\ConferenceRepository;
use App\Repository\ParticipationRepository;
use App\Repository\SubmitRepository;
use App\UX\UserChartBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private ParticipationRepository $participationRepository,
        private SubmitRepository $submitRepository,
        private UserChartBuilder $userChartBuilder,
    ) {
    }

    #[Route(path: '/user/account', name: 'user_account')]
    public function userAccount(): Response
    {
        $pastSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE);
        $futureSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED);
        $pastConferences = $this->conferenceRepository->findAttentedConferencesByUser($this->getUser());
        $futureParticipations = $this->participationRepository->findFutureParticipationsByUser($this->getUser());

        if (0 !== \count($pastSubmits) + \count($futureSubmits) + \count($pastConferences) + \count($futureParticipations)) {
            $chart = $this->userChartBuilder->buildUserChart($pastSubmits, $futureSubmits, $pastConferences, $futureParticipations);
        }

        return $this->render('user/account.html.twig', [
            'chart' => $chart ?? null,
        ]);
    }
}
