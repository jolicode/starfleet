<?php

namespace App\Controller\Admin;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Conference;
use Symfony\Component\HttpFoundation\Response;

class ConferenceController extends EasyAdminController
{
    public function excludeConferenceAction(): Response
    {
        /** @var Conference $conference */
        $conference = $this->request->attributes->get('easyadmin')['item'];
        $conference->setExcluded(true);

        $this->em->flush();

        $this->addFlash('info', 'Conference excluded');

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

        $this->addFlash('info', 'Conference included again');

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'ExcludedConference',
            'page' => $this->request->query->get('page'),
        ]);
    }
}
