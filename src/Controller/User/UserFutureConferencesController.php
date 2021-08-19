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

use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserFutureConferencesController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    ) {
    }

    #[Route(path: '/user/future-conferences', name: 'user_conferences')]
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
