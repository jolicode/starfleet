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

use App\DataTransformer\ConferenceNameTransformer;
use App\Entity\Participation;
use App\Repository\ConferenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipationType extends AbstractType
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private ConferenceNameTransformer $transformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conference', ConferenceDatalistType::class)
            ->add('asSpeaker', CheckboxType::class)
            ->add('transportStatus', ChoiceType::class, [
                'choices' => Participation::STATUSES,
            ])
            ->add('hotelStatus', ChoiceType::class, [
                'choices' => Participation::STATUSES,
            ])
            ->add('conferenceTicketStatus', ChoiceType::class, [
                'choices' => Participation::STATUSES,
            ])
        ;

        $builder
            ->get('conference')
            ->addModelTransformer($this->transformer)
        ;
    }
}
