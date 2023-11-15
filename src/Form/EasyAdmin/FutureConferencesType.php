<?php

namespace App\Form\EasyAdmin;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FutureConferencesType extends AbstractType
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Conference::class,
            'query_builder' => $this->conferenceRepository->getFutureConferencesQueryBuilder(),
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
