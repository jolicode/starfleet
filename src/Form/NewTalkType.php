<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Submit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class NewTalkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('intro', TextareaType::class)
            ->add('conference', ConferenceDatalistType::class, [
                'mapped' => false,
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'mapped' => false,
            ])
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $talk = $event->getData();
                    $form = $event->getForm();

                    $submit = new Submit();
                    $submit
                        ->setTalk($talk)
                        ->setConference($form->get('conference')->getData())
                    ;

                    foreach ($form->get('users')->getData() as $user) {
                        $submit->addUser($user);
                    }

                    $talk->addSubmit($submit);
                },
                -1
            )
        ;
    }
}
