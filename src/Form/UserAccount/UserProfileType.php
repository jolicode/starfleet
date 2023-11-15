<?php

namespace App\Form\UserAccount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProfileType extends AbstractType
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class)
            ->add('previousPassword', PasswordType::class, [
                'always_empty' => true,
                'mapped' => false,
                'required' => false,
            ])
            ->add('newPassword', PasswordType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('job', TextType::class, [
                'required' => false,
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => '6',
                ],
            ])
            ->add('twitterAccount', TextType::class, [
                'required' => false,
            ])
            ->add('tshirtSize', ChoiceType::class, [
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'choices' => [
                    'XS' => 'XS',
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    'XXL' => 'XXL',
                ],
                'label' => 'T-shirt size',
            ])
            ->add('foodPreferences', TextAreaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => '3',
                ],
            ])
            ->add('allergies', TextAreaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => '3',
                ],
            ])
            ->addEventListener(
                FormEvents::SUBMIT,
                [$this, 'onPasswordSubmit'],
                -1
            )
        ;
    }

    public function onPasswordSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $user = $event->getData();

        if ($passwordData = $form->get('password')->getData()) {
            if (!$previousPasswordData = $form->get('previousPassword')->getData()) {
                $form->get('previousPassword')->addError(new FormError('Invalid password.'));

                return;
            }

            if (!$this->hasher->isPasswordValid($user, $previousPasswordData)) {
                $form->get('previousPassword')->addError(new FormError('Invalid password.'));

                return;
            }

            if (\strlen($passwordData) < 7) {
                $form->get('password')->addError(new FormError('Your password must have at least 6 characters.'));

                return;
            }

            $user->setPassword($this->hasher->hashPassword($user, $passwordData));
        }
    }
}
