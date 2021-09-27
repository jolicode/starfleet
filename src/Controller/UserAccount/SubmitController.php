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

use App\Entity\Conference;
use App\Entity\Submit;
use App\Event\Notification\NewSubmitEvent;
use App\Event\Notification\SubmitCancelledEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use App\Form\UserAccount\SubmitType;
use App\Repository\ConferenceRepository;
use App\Repository\SubmitRepository;
use App\UX\UserChartBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SubmitController extends AbstractController
{
    public function __construct(
        private SubmitRepository $submitRepository,
        private ConferenceRepository $conferenceRepository,
        private UserChartBuilder $userChartBuilder,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(path: '/user/submits', name: 'user_submits')]
    public function userSubmits(Request $request): Response
    {
        $submit = new Submit();
        $submit->addUser($this->getUser());
        $form = $this->createForm(SubmitType::class, $submit, [
            'validation_groups' => ['Default', 'user_account'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($submit);

            if (\count($submit->getUsers()) > 1) {
                $this->eventDispatcher->dispatch(new NewSubmitEvent($submit));
            }

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
        $preEditUsers = $submit->getUsers();

        $form = $this->createForm(SubmitType::class, $submit, [
            'validation_groups' => ['Default', 'user_account'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $newUsers = [];

            foreach ($submit->getUsers() as $user) {
                if (!$preEditUsers->contains($user)) {
                    $newUsers[] = $user;
                }
            }

            if (\count($newUsers)) {
                $this->eventDispatcher->dispatch(new NewSubmitEvent($submit));
            }

            $this->em->flush();
            $this->addFlash('info', 'Your talk has been submitted.');

            return $this->redirectToRoute('user_submits');
        }

        return $this->render('/user/submit/submit_form.html.twig', [
            'form' => $form->createView(),
            'action' => 'edit',
        ]);
    }

    #[Route(path: '/user/submit-new/{id}', name: 'new_submit')]
    public function newSubmit(Conference $conference, Request $request): Response
    {
        $submit = new Submit();
        $submit->addUser($this->getUser());
        $submit->setConference($conference);
        $form = $this->createForm(SubmitType::class, $submit, [
            'validation_groups' => ['Default', 'user_account'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($submit);

            if (\count($submit->getUsers()) > 1) {
                $this->eventDispatcher->dispatch(new NewSubmitEvent($submit));
            }

            $this->em->flush();
            $this->addFlash('info', 'Your talk has been submitted.');

            return $this->redirectToRoute('user_submits');
        }

        return $this->render('/user/submit/submit_form.html.twig', [
            'form' => $form->createView(),
            'action' => 'new',
        ]);
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-accept/{id}', name: 'user_submit_accept', methods: ['POST'])]
    public function acceptSubmit(Submit $submit, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $submit->setStatus(Submit::STATUS_ACCEPTED);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($submit));
        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as accepted.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-done/{id}', name: 'user_submit_done', methods: ['POST'])]
    public function doneSubmit(Submit $submit, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $submit->setStatus(Submit::STATUS_DONE);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($submit));
        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as done.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-reject/{id}', name: 'user_submit_reject', methods: ['POST'])]
    public function rejectSubmit(Submit $submit, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $submit->setStatus(Submit::STATUS_REJECTED);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($submit));
        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as rejected.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-pending/{id}', name: 'user_submit_pending', methods: ['POST'])]
    public function pendingSubmit(Submit $submit, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $submit->setStatus(Submit::STATUS_PENDING);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($submit));
        $this->em->flush();
        $this->addFlash('info', sprintf('Submit for %s tagged as pending.', $submit->getConference()->getName()));

        return $this->redirectToRoute('user_submits');
    }

    #[IsGranted('SUBMIT_ACTION', 'submit')]
    #[Route(path: '/user/submit-cancel/{id}', name: 'user_submit_cancel', methods: ['POST'])]
    public function cancelSubmit(Submit $submit, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $this->eventDispatcher->dispatch(new SubmitCancelledEvent($submit));

        $this->em->remove($submit);
        $this->em->flush();
        $this->addFlash('info', 'Submit has been cancelled.');

        return $this->redirectToRoute('user_submits');
    }
}
