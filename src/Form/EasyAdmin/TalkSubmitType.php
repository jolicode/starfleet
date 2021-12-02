<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Form\EasyAdmin;

use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TalkSubmitType extends AbstractType
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('submittedAt', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('conference', EasyAdminAutocompleteType::class, [
                'class' => Conference::class,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(Submit::STATUS_EMOJIS),
                'expanded' => true,
                'data' => Submit::STATUS_PENDING,
            ])
            ->add('talk', EasyAdminAutocompleteType::class, [
                'label' => false,
                'class' => Talk::class,
                'data_class' => null,
                'data' => $this->requestStack->getMasterRequest()->attributes->get('easyadmin')['item'],
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $submit = $event->getData();
            if ($submit) {
                $form
                    ->add('status', ChoiceType::class, [
                        'choices' => array_flip(Submit::STATUS_EMOJIS),
                        'expanded' => true,
                        'data' => $submit->getStatus(),
                    ])
                    ->add('users', EasyAdminAutocompleteType::class, [
                        'class' => User::class,
                        'multiple' => true,
                        'data' => $submit->getUsers()->toArray(),
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Submit::class,
        ]);
    }
}
