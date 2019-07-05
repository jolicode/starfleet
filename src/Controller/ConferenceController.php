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

use App\Entity\Conference;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    protected $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/", name="conferences_list")
     */
    public function listAction(Request $request)
    {
        $conferences = $this->registry->getRepository(Conference::class)->findAttendedConferences();

        return $this->renderList($conferences);
    }

    /**
     * @Route("/conferences/tagged/{tag}", name="conferences_list_by_tag")
     */
    public function listByTagAction(Request $request, string $tag)
    {
        $conferences = $this->registry->getRepository(Conference::class)->findAttendedConferencesByTag($tag);

        if (!$conferences) {
            $this->addFlash('info', 'No conferences found for tag "'.$tag.'"');

            return $this->redirectToRoute('conferences_list');
        }

        return $this->renderList($conferences);
    }

    /**
     * @Route("/conferences/{slug}", name="conferences_show")
     */
    public function showAction(Request $request, string $slug)
    {
        $conference = $this->registry->getRepository(Conference::class)->findOneAttended($slug);

        if (!$conference) {
            throw $this->createNotFoundException('Conference not found.');
        }

        return $this->render('conferences/show.html.twig', [
            'conference' => $conference,
        ]);
    }

    /**
     * @param Conference[] $conferences
     */
    private function renderList(array $conferences): Response
    {
        $futureConferences = $pastConferences = $liveConferences = [];

        $startOfDay = (new \DateTime())->setTime(0, 0, 0);
        $endOfDay = (new \DateTime())->setTime(23, 59, 59);

        foreach ($conferences as $conference) {
            if ($conference->getEndAt() < $startOfDay) {
                $pastConferences[] = $conference;
            } elseif ($conference->getStartAt() > $endOfDay) {
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
