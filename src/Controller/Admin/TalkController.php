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
use App\Entity\Submit;
use App\Entity\Talk;
use App\Event\NewTalkSubmittedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;

class TalkController extends EasyAdminController
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function persistTalkEntity(Talk $talk, Form $newForm): void
    {
        $conferences = $newForm->get('conferences')->getData();
        $authors = $newForm->get('authors')->getData();
        $newSubmits = [];

        foreach ($conferences as $conference) {
            if ($conference instanceof Conference) {
                $submit = new Submit();
                $submit->setCreatedAt(new \DateTime());
                $submit->setUpdatedAt(new \DateTime());
                $submit->setSubmittedAt(new \DateTime());
                $submit->setConference($conference);
                $submit->setTalk($talk);
                $submit->setStatus(Submit::STATUS_PENDING);

                foreach ($authors['authors'] as $author) {
                    $submit->addUser($author);
                }

                $this->em->persist($submit);
                $newSubmits[] = $submit;
            }
        }

        $this->eventDispatcher->dispatch(new NewTalkSubmittedEvent($talk, $newSubmits));

        $this->em->persist($talk);
        $this->em->flush();
    }
}
