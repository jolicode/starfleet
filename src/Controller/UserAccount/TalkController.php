<?php

namespace App\Controller\UserAccount;

use App\Entity\Talk;
use App\Entity\User;
use App\Event\NewTalkSubmittedEvent;
use App\Form\UserAccount\EditTalkType;
use App\Form\UserAccount\NewTalkType;
use App\Repository\TalkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Markup;

class TalkController extends AbstractController
{
    public function __construct(
        private TalkRepository $talkRepository,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route('/user/talks', name: 'user_talks')]
    public function userTalks(Request $request): Response
    {
        $talk = new Talk();
        $form = $this->createForm(NewTalkType::class, $talk);
        $form->get('users')->setData([$this->getUser()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($talk);
            $this->em->flush();

            $message = new Markup(
                $this->renderView('flashes/created.html.twig', [
                    'entity' => 'Talk',
                    'controller_action' => 'show_talk',
                    'entity_id' => $talk->getId(),
                ]),
                'UTF-8',
            );

            $this->addFlash('info', $message);
            $this->eventDispatcher->dispatch(new NewTalkSubmittedEvent($talk, $talk->getSubmits()->toArray()));

            return $this->redirectToRoute('user_talks');
        }

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/talk/talk.html.twig', [
            'talks' => $this->talkRepository->findUserTalks($user),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/talks/all', name: 'user_talks_all')]
    public function userTalksAll(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/talk/all.html.twig', [
            'talks' => $this->talkRepository->findUserTalks($user),
        ]);
    }

    #[IsGranted(data: 'TALK_ACTION', subject: 'talk')]
    #[Route(path: '/user/talk-edit/{id}', name: 'edit_talk')]
    public function editTalk(Talk $talk, Request $request): Response
    {
        $form = $this->createForm(EditTalkType::class, $talk);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($talk);
            $this->em->flush();

            $message = new Markup(
                $this->renderView('flashes/edited.html.twig', [
                    'entity' => 'Talk',
                    'controller_action' => 'show_talk',
                    'entity_id' => $talk->getId(),
                ]),
                'UTF-8',
            );

            $this->addFlash('info', $message);

            return $this->redirectToRoute('user_talks');
        }

        return $this->render('/user/talk/talk_edit.html.twig', [
            'talk' => $talk,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(data: 'TALK_ACTION', subject: 'talk')]
    #[Route(path: '/user/talk-show/{id}', name: 'show_talk')]
    public function showTalk(Talk $talk): Response
    {
        return $this->render('/user/talk/talk_show.html.twig', [
            'talk' => $talk,
        ]);
    }
}
