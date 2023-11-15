<?php

namespace App\Form\UserAccount;

use App\Entity\Talk;
use App\Entity\User;
use App\Repository\TalkRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class SubmitType extends AbstractType
{
    public function __construct(
        private Security $security,
        private TalkRepository $talkRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('conference', ConferenceDatalistType::class)
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
            ])
            ->add('talk', EntityType::class, [
                'class' => Talk::class,
                'choices' => [
                    'Talks you already gave' => $this->talkRepository->findUserTalks($user),
                    'Talks you never gave' => $this->talkRepository->findNonUserTalks($user),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($user) {
                if (!$event->getForm()->isValid()) {
                    return;
                }

                $submit = $event->getData();
                $submit->setSubmittedBy($user);
            })
        ;
    }
}
