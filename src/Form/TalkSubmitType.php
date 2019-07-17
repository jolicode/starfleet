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

use App\Entity\Conference;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TalkSubmitType extends AbstractType
{
    private $user;
    private $requestStack;

    public function __construct(TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('submittedAt', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(Submit::STATUS_EMOJIS),
                'expanded' => true,
                'data' => Submit::STATUS_PENDING,
            ])
            ->add('users', EasyAdminAutocompleteType::class, [
                'class' => User::class,
                'multiple' => true,
                'data' => [$this->user],
            ])
            ->add('conference', EasyAdminAutocompleteType::class, [
                'class' => Conference::class,
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Submit::class,
        ]);
    }
}
