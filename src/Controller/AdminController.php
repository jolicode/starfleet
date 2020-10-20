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
use App\Entity\Conference;
use App\Entity\Participation;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Enum\Workflow\Transition\Participation as ParticipationTransition;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;

class AdminController extends EasyAdminController
{
    protected $workflowRegistry;
    protected $logger;

    public function __construct(WorkflowRegistry $workflowRegistry, LoggerInterface $logger)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->logger = $logger;
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

    /**
     * @Route(path="/participation/accept", name="participation_accept")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/buy_ticket", name="participation_buy_ticket")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/reserve_transport", name="participation_reserve_transport")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/book_hotel", name="participation_book_hotel")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/validate", name="participation_validate")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/reject", name="participation_reject")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    /**
     * @Route(path="/participation/cancel", name="participation_cancel")
     * @Security("has_role('ROLE_ADMIN')")
     */
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

    protected function persistTalkEntity(Talk $talk, Form $newForm)
    {
        $conference = $newForm->get('conference')->getData();

        if ($conference instanceof Conference) {
            $submit = new Submit();
            $submit->setCreatedAt(new \DateTime());
            $submit->setUpdatedAt(new \DateTime());
            $submit->setSubmittedAt(new \DateTime());
            $submit->setConference($conference);
            $submit->setTalk($talk);
            $submit->setStatus(Submit::STATUS_PENDING);
            $submit->addUser($this->getUser());

            $this->em->persist($submit);
        }

        $this->em->persist($talk);
        $this->em->flush();
    }
}
