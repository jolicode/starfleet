<?php

namespace App\Controller\UserAccount;

use App\Entity\Submit;
use App\Entity\User;
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
        /** @var User $user */
        $user = $this->getUser();

        $pastSubmits = $this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_DONE);
        $futureSubmits = $this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_ACCEPTED);
        $pastConferences = $this->conferenceRepository->findAttentedConferencesByUser($user);
        $futureParticipations = $this->participationRepository->findFutureParticipationsByUser($user);

        if (0 !== \count($pastSubmits) + \count($futureSubmits) + \count($pastConferences) + \count($futureParticipations)) {
            $chart = $this->userChartBuilder->buildUserChart($pastSubmits, $futureSubmits, $pastConferences, $futureParticipations);
        }

        return $this->render('user/account.html.twig', [
            'chart' => $chart ?? null,
        ]);
    }
}
