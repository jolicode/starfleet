<?php

namespace App\Form\UserAccount;

use App\DataTransformer\ConferenceNameTransformer;
use App\Repository\ConferenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConferenceDatalistType extends AbstractType
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private ConferenceNameTransformer $transformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices' => $this->conferenceRepository
                    ->getFutureConferencesQueryBuilder()
                    ->getQuery()
                    ->execute(),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'invalid_message' => 'Submitted conference could not be found',
            ])
        ;
    }

    public function getParent(): string
    {
        return DatalistType::class;
    }
}
