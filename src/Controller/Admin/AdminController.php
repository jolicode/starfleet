<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Conference;
use App\Entity\Participation;
use App\Enum\Workflow\Transition\Participation as ParticipationTransition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;

class AdminController extends EasyAdminController
{
    public function __construct(
        private WorkflowRegistry $workflowRegistry,
    ) {
    }

    public function excludeConferenceAction(): Response
    {
        /** @var Conference $conference */
        $conference = $this->request->attributes->get('easyadmin')['item'];
        $conference->setExcluded(true);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
            'page' => $this->request->query->get('page'),
        ]);
    }

    public function includeConferenceAction(): Response
    {
        /** @var Conference $conference */
        $conference = $this->request->attributes->get('easyadmin')['item'];
        $conference->setExcluded(false);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
            'page' => $this->request->query->get('page'),
        ]);
    }

    #[Route(path: '/participation/accept', name: 'participation_accept')]
    #[Security('ROLE_ADMIN')]
    public function acceptAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::ACCEPT);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/buy_ticket', name: 'participation_buy_ticket')]
    #[Security('ROLE_ADMIN')]
    public function buy_ticketAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::BUY_TICKET);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/reserve_transport', name: 'participation_reserve_transport')]
    #[Security('ROLE_ADMIN')]
    public function reserve_transportAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::RESERVE_TRANSPORT);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/book_hotel', name: 'participation_book_hotel')]
    #[Security('ROLE_ADMIN')]
    public function book_hotelAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::BOOK_HOTEL);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/validate', name: 'participation_validate')]
    #[Security('ROLE_ADMIN')]
    public function validateAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::VALIDATE);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/reject', name: 'participation_reject')]
    #[Security('ROLE_ADMIN')]
    public function rejectAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::REJECT);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    #[Route(path: '/participation/cancel', name: 'participation_cancel')]
    #[Security('ROLE_ADMIN')]
    public function cancelAction(): Response
    {
        /** @var Participation $participation */
        $participation = $this->request->attributes->get('easyadmin')['item'];
        $workflow = $this->workflowRegistry->get($participation, 'participation_request');

        $workflow->apply($participation, ParticipationTransition::CANCEL);

        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }
}
