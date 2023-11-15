<?php

namespace App\Controller\UserAccount;

use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FutureConferencesController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    ) {
    }

    #[Route(path: '/user/future-conferences', name: 'future_conferences')]
    public function futureConferences(): Response
    {
        $conferences = $this->conferenceRepository->getUserFutureConferences();
        $featuredConferences = $this->conferenceRepository->findFeaturedConferences();

        return $this->render('user/future_conferences/future_conferences.html.twig', [
            'conferences' => $conferences,
            'featuredConferences' => $featuredConferences,
        ]);
    }
}
