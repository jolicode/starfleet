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

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Participation;
use App\Enum\Workflow\Transition\Participation as ParticipationTransition;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;
use Symfony\Component\Workflow\Transition;

class AdminController extends EasyAdminController
{
    protected $workflowRegistry;
    protected $doctrineRegistry;
    protected $logger;

    public function __construct(WorkflowRegistry $workflowRegistry, RegistryInterface $doctrineRegistry, LoggerInterface $logger)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->doctrineRegistry = $doctrineRegistry;
        $this->logger = $logger;
    }

    /**
     * @Route(path="/admin/participation/accept", name="participation_accept")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function acceptAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::ACCEPT);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/buy_ticket", name="participation_buy_ticket")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function buy_ticketAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::BUY_TICKET);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/reserve_transport", name="participation_reserve_transport")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function reserve_transportAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::RESERVE_TRANSPORT);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/book_hotel", name="participation_book_hotel")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function book_hotelAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::BOOK_HOTEL);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/validate", name="participation_validate")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function validateAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::VALIDATE);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/reject", name="participation_reject")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function rejectAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::REJECT);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    /**
     * @Route(path="/admin/participation/cancel", name="participation_cancel")
     * @Security("has_role('ROLE_ADMIN')")
     */
    protected function cancelAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::CANCEL);

        $this->doctrineRegistry->getManager()->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }
}
