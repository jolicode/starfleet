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
use App\Form\SubmitType;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use App\UX\UserChartBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserSubmitController extends AbstractController
{
    public function __construct(
        private SubmitRepository $submitRepository,
        private ConferenceRepository $conferenceRepository,
        private UserChartBuilder $userChartBuilder,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/user/submits', name: 'user_submits')]
    public function userSubmits(Request $request): Response
    {
        $submit = new Submit();
        $submit->addUser($this->getUser());
        $form = $this->createForm(SubmitType::class, $submit);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($submit);
            $this->em->flush();

            $this->addFlash('info', 'The submit has been saved.');

            return $this->redirectToRoute('user_submits');
        }

        $pendingSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING);
        $doneSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE);
        $rejectedSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED);
        $futureSubmits = $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED);

        if (0 !== \count($pendingSubmits) + \count($doneSubmits) + \count($rejectedSubmits) + \count($futureSubmits)) {
            $chart = $this->userChartBuilder->buildSubmitsChart($pendingSubmits, $doneSubmits, $rejectedSubmits, $futureSubmits);
        }

        return $this->render('user/submit/submit.html.twig', [
            'pendingSubmits' => $pendingSubmits,
            'doneSubmits' => $doneSubmits,
            'rejectedSubmits' => $rejectedSubmits,
            'futureSubmits' => $futureSubmits,
            'chart' => $chart ?? null,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-accepted/{id}', name: 'user_submit_accepted')]
    public function acceptSubmit(Submit $submit): Response
    {
        $submit->setStatus(Submit::STATUS_ACCEPTED);

        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as accepted.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-done/{id}', name: 'user_submit_done')]
    public function doneSubmit(Submit $submit): Response
    {
        $submit->setStatus(Submit::STATUS_DONE);

        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as done.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-rejected/{id}', name: 'user_submit_rejected')]
    public function rejectSubmit(Submit $submit): Response
    {
        $submit->setStatus(Submit::STATUS_REJECTED);

        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as rejected.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-pending/{id}', name: 'user_submit_pending')]
    public function pendingSubmit(Submit $submit): Response
    {
        $submit->setStatus(Submit::STATUS_PENDING);

        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as pending.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[Route(path: '/user/accepted-submits', name: 'accepted_submits')]
    public function futureSubmits(): Response
    {
        return $this->render('user/submit/accepted_submits.html.twig', [
            'submits' => $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_ACCEPTED),
        ]);
    }

    #[Route(path: '/user/pending-submits', name: 'pending_submits')]
    public function pendingSubmits(): Response
    {
        return $this->render('user/submit/pending_submits.html.twig', [
            'submits' => $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_PENDING),
        ]);
    }

    #[Route(path: '/user/done-submits', name: 'done_submits')]
    public function doneSubmits(): Response
    {
        return $this->render('user/submit/done_submits.html.twig', [
            'submits' => $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_DONE),
        ]);
    }

    #[Route(path: '/user/rejected-submits', name: 'rejected_submits')]
    public function rejectedSubmits(): Response
    {
        return $this->render('user/submit/rejected_submits.html.twig', [
            'submits' => $this->submitRepository->findUserSubmitsByStatus($this->getUser(), Submit::STATUS_REJECTED),
        ]);
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-edit/{id}', name: 'edit_submit')]
    public function editSubmit(Submit $submit, Request $request): Response
    {
        $form = $this->createForm(SubmitType::class, $submit);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($submit);
            $this->em->flush();

            $this->addFlash('info', 'Your talk has been submitted.');

            return $this->redirectToRoute('user_submits');
        }

        return $this->render('/user/submit/submit_edit.html.twig', [
            'submit' => $submit,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-remove/{id}', name: 'remove_submit')]
    public function removeSubmit(Submit $submit): Response
    {
        $this->em->remove($submit);
        $this->em->flush();
        $this->addFlash('info', 'Submit has been removed.');

        return $this->redirectToRoute('user_submits');
    }
}
