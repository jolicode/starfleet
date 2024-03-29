<?php

namespace App\Controller\Admin;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Participation;
use App\Entity\User;
use App\Enum\Workflow\Transition\Participation as ParticipationTransition;
use App\Event\Notification\ParticipationStatusChangedEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;

class ParticipationController extends EasyAdminController
{
    public function __construct(
        private WorkflowRegistry $workflowRegistry,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(path: '/participation/accept', name: 'participation_accept')]
    #[IsGranted('ROLE_ADMIN')]
    public function acceptedAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::ACCEPTED);
        $this->eventDispatcher->dispatch(new ParticipationStatusChangedEvent($participation));

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/reject', name: 'participation_reject')]
    #[IsGranted('ROLE_ADMIN')]
    public function rejectedAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::REJECTED);
        $this->eventDispatcher->dispatch(new ParticipationStatusChangedEvent($participation));

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/cancel', name: 'participation_cancel')]
    #[IsGranted('ROLE_ADMIN')]
    public function cancelledAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');
        $workflow->apply($participation, ParticipationTransition::CANCELLED);
        $this->eventDispatcher->dispatch(new ParticipationStatusChangedEvent($participation));

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    protected function createNewParticipationEntity(): Participation
    {
        /** @var User $user */
        $user = $this->getUser();

        return new Participation($user);
    }
}
