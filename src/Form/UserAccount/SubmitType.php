<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Form\UserAccount;

use App\Entity\Talk;
use App\Entity\User;
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
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conference', ConferenceDatalistType::class)
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
            ])
            ->add('talk', EntityType::class, [
                'class' => Talk::class,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                if (!$event->getForm()->isValid()) {
                    return;
                }

                $submit = $event->getData();
                $submit->setSubmittedBy($this->security->getUser());
            })
        ;
    }
}
