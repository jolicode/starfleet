<?php

namespace App\Controller\Admin;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Event\NewTalkSubmittedEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;

class TalkController extends EasyAdminController
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
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
                $submit->resetStatusChanged();

                foreach ($authors['authors'] as $author) {
                    $submit->addUser($author);
                }

                $this->em->persist($submit);
                $newSubmits[] = $submit;
            }
        }

        $this->em->persist($talk);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new NewTalkSubmittedEvent($talk, $newSubmits));
    }

    protected function updateTalkEntity(Talk $talk): void
    {
        foreach ($talk->getSubmits() as $submit) {
            if ($submit->hasStatusChanged()) {
                $this->eventDispatcher->dispatch(new SubmitStatusChangedEvent($submit));
                $submit->resetStatusChanged();
            }
        }

        parent::updateEntity($talk);
    }
}
