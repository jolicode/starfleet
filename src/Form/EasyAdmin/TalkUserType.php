<?php

namespace App\Form\EasyAdmin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TalkUserType extends AbstractType
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authors', EasyAdminAutocompleteType::class, [
                'label' => false,
                'class' => User::class,
                'multiple' => true,
                'data' => [$this->tokenStorage->getToken()->getUser()],
            ])
        ;
    }
}
