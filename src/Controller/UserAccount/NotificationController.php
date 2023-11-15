<?php

namespace App\Controller\UserAccount;

use App\Entity\Notifications\AbstractNotification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notificationRepository,
    ) {
    }

    #[IsGranted('NOTIFICATION_READ', 'notification')]
    #[Route(path: '/user/read-notification/{id}', name: 'read_notification', methods: ['POST'])]
    public function readNotification(AbstractNotification $notification, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $notification->markAsRead();
        $this->em->flush();
        $this->addFlash('info', 'The requested notification was marked as read.');

        if ($previousUrl = $request->get('previousUrl')) {
            return $this->redirect($previousUrl);
        }

        return $this->redirectToRoute('user_account');
    }

    #[Route(path: '/user/read-all-notification', name: 'read_all_notification', methods: ['POST'])]
    public function readAllNotification(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $this->notificationRepository->markAllAsReadForUser($user);
        $this->em->flush();
        $this->addFlash('info', 'All your notifications are now marked as read.');

        if ($previousUrl = $request->get('previousUrl')) {
            return $this->redirect($previousUrl);
        }

        return $this->redirectToRoute('user_account');
    }

    #[Route(path: '/user/notification-all-unread', name: 'notification_all_unread')]
    public function allUnreadNotifications(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/notifications/_all_unread.html.twig', [
            'unreadNotifications' => $this->notificationRepository->getAllUnreadForUser($user),
        ]);
    }
}
