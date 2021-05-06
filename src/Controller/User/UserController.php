<?php

namespace App\Controller\User;

use App\Entity\Submit;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private SubmitRepository $submitRepository,
    ) {
    }

    #[Route(path: '/user/account', name: 'user_account')]
    public function userAccount(): Response
    {
        $user = $this->getUser();
        $attendedConferences = $this->conferenceRepository->findAttentedConferencesByUser($user);
        $pendingSubmits = $this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_PENDING);
        $acceptedSubmits = $this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_ACCEPTED);
        $rejectedSubmits = $this->submitRepository->findUserSubmitsByStatus($user, Submit::STATUS_REJECTED);
        $upcomingSubmits = $this->submitRepository->findUserUpcomingUserSubmits($user);

        return $this->render('user/account.html.twig', [
            'user' => $user,
            'attendedConferences' => $attendedConferences,
            'pendingSubmits' => $pendingSubmits,
            'acceptedSubmits' => $acceptedSubmits,
            'rejectedSubmits' => $rejectedSubmits,
            'upcomingSubmits' => $upcomingSubmits,
        ]);
    }
}
