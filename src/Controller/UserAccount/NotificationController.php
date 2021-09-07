<?php
namespace App\Controller\UserAccount;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notifications\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notificationRepository,
    ) {
    }

    #[IsGranted('READ_NOTIFICATION', 'notification')]
    #[Route(path: '/user/read-notification/{id}', name: 'read_notification', methods: ["POST"])]
    public function readNotification(Notification $notification, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $notification->setRead(true);
        $this->em->flush();
        $this->addFlash('info', 'Notification marked as read.');

        return $this->redirectToRoute($request->query->get('route_name'));
    }

    #[Route(path: '/user/read-all-notification', name: 'read_all_notification', methods: ["POST"])]
    public function readAllNotification(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        $this->notificationRepository->markAllAsRead($this->getUser());

        $this->em->flush();
        $this->addFlash('info', 'All notifications marked as read.');

        return $this->redirectToRoute($request->query->get('route_name'));
    }
}
