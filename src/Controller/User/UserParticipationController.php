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

use App\Entity\Conference;
use App\Entity\Participation;
use App\Enum\Workflow\Transition\Participation as ParticipationTransition;
use App\Form\UserAccount\ParticipationType;
use App\Repository\ConferenceRepository;
use App\Repository\ParticipationRepository;
use App\UX\UserChartBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\StateMachine;

class UserParticipationController extends AbstractController
{
    public function __construct(
        private ParticipationRepository $participationRepository,
        private ConferenceRepository $conferenceRepository,
        private UserChartBuilder $userChartBuilder,
        private EntityManagerInterface $em,
        private StateMachine $workflow,
    ) {
    }

    #[Route(path: '/user/participations', name: 'user_participations')]
    public function userParticipation(Request $request): Response
    {
        $participation = new Participation($this->getUser());
        $form = $this->createForm(ParticipationType::class, $participation);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($participation);
            $this->em->flush();

            $this->addFlash('info', 'Participation Submitted.');

            return $this->redirectToRoute('user_participations');
        }

        $futureParticipations = $this->participationRepository->findFutureParticipationsByUser($this->getUser());
        $pendingParticipations = $this->participationRepository->findPendingParticipationsByUser($this->getUser());
        $rejectedParticipations = $this->participationRepository->findRejectedParticipationsByUser($this->getUser());
        $pastParticipations = $this->participationRepository->findPastParticipationsByUser($this->getUser());

        if (0 !== \count($pendingParticipations) + \count($futureParticipations) + \count($rejectedParticipations) + \count($pastParticipations)) {
            $chart = $this->userChartBuilder->buildParticipationsChart($pendingParticipations, $futureParticipations, $rejectedParticipations, $pastParticipations);
        }

        return $this->render('user/participation/participation.html.twig', [
            'form' => $form->createView(),
            'futureParticipations' => $futureParticipations,
            'pendingParticipations' => $pendingParticipations,
            'rejectedParticipations' => $rejectedParticipations,
            'pastParticipations' => $pastParticipations,
            'chart' => $chart ?? null,
        ]);
    }

    #[Route(path: '/user/future-participations', name: 'future_participations')]
    public function futureParticipations(): Response
    {
        return $this->render('user/participation/future_participations.html.twig', [
            'participations' => $this->participationRepository->findFutureParticipationsByUser($this->getUser()),
        ]);
    }

    #[Route(path: '/user/pending-participations', name: 'pending_participations')]
    public function pendingParticipations(): Response
    {
        return $this->render('user/participation/pending_participations.html.twig', [
            'participations' => $this->participationRepository->findPendingParticipationsByUser($this->getUser()),
        ]);
    }

    #[Route(path: '/user/past-participations', name: 'past_participations')]
    public function pastParticipations(): Response
    {
        return $this->render('user/participation/past_participations.html.twig', [
            'participations' => $this->participationRepository->findPastParticipationsByUser($this->getUser()),
        ]);
    }

    #[Route(path: '/user/rejected-participations', name: 'rejected_participations')]
    public function rejectedParticipations(): Response
    {
        return $this->render('user/participation/rejected_participations.html.twig', [
            'participations' => $this->participationRepository->findRejectedParticipationsByUser($this->getUser()),
        ]);
    }

    #[Route(path: '/user/participation-new/{id}', name: 'new_participation')]
    public function newParticipation(Conference $conference, Request $request): Response
    {
        $participation = new Participation($this->getUser());
        $participation->setConference($conference);
        $form = $this->createForm(ParticipationType::class, $participation);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($participation);
            $this->em->flush();

            $this->addFlash('info', 'Your participation has been submitted.');

            return $this->redirectToRoute('user_participations');
        }

        return $this->render('user/participation/participation_form.html.twig', [
            'form' => $form->createView(),
            'action' => 'new',
        ]);
    }

    #[IsGranted(data: 'PARTICIPATION_ACTION', subject: 'participation')]
    #[Route(path: '/user/participation-edit/{id}', name: 'edit_participation')]
    public function editParticipation(Participation $participation, Request $request): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation, [
            'validation_groups' => ['edition'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', 'Your participation has been submitted.');

            return $this->redirectToRoute('user_participations');
        }

        return $this->render('/user/participation/participation_form.html.twig', [
            'form' => $form->createView(),
            'action' => 'edit',
        ]);
    }

    #[IsGranted(data : 'PARTICIPATION_ACTION', subject: 'participation')]
    #[Route(path: '/user/participation-cancel/{id}', name: 'user_participation_cancel', methods: ['POST'])]
    public function cancelParticipation(Participation $participation, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        try {
            $this->workflow->apply($participation, ParticipationTransition::CANCELLED);
        } catch (TransitionException $exception) {
            $this->addFlash(
                'error',
                sprintf('Participation cancel has failed : %s', $exception->getMessage())
            );

            return $this->redirectToRoute('user_participations');
        }

        $this->em->flush();
        $this->addFlash('info', 'Your participation has been cancelled.');

        return $this->redirectToRoute('user_participations');
    }
}
