<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller;

use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="conferences_list")
     */
    public function listAction(ConferenceRepository $conferenceRepository)
    {
        $conferences = $conferenceRepository->findAttendedConferences();

        return $this->renderList($conferences);
    }

    /**
     * @Route("/conferences/tagged/{tag}", name="conferences_list_by_tag")
     */
    public function listByTagAction(string $tag, ConferenceRepository $conferenceRepository)
    {
        $conferences = $conferenceRepository->findAttendedConferencesByTag($tag);

        if (!$conferences) {
            $this->addFlash('info', 'No conferences found for tag "'.$tag.'"');

            return $this->redirectToRoute('conferences_list');
        }

        return $this->renderList($conferences);
    }

    /**
     * @Route("/conferences/{slug}", name="conferences_show")
     */
    public function showAction(string $slug, ConferenceRepository $conferenceRepository)
    {
        $conference = $conferenceRepository->findOneAttended($slug);

        if (!$conference) {
            throw $this->createNotFoundException("Conference with slug '$slug' not found.");
        }

        return $this->render('conferences/show.html.twig', [
            'conference' => $conference,
        ]);
    }

    private function renderList(array $conferences): Response
    {
        $futureConferences = $pastConferences = $liveConferences = [];

        $startOfToday = (new \DateTime())->setTime(0, 0, 0);
        $endOfToday = (new \DateTime())->setTime(23, 59, 59);

        foreach ($conferences as $conference) {
            if ($conference->getEndAt() < $startOfToday) {
                $pastConferences[] = $conference;
            } elseif ($conference->getStartAt() > $endOfToday) {
                $futureConferences[] = $conference;
            } else {
                $liveConferences[] = $conference;
            }
        }

        return $this->render('conferences/list.html.twig', [
            'futureConferences' => $futureConferences,
            'pastConferences' => $pastConferences,
            'liveConferences' => $liveConferences,
        ]);
    }
}
