<?php

namespace App\Form\UserAccount;

use App\Entity\Participation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conference', ConferenceDatalistType::class, [
                'disabled' => $options['validation_groups'] && \in_array('edition', $options['validation_groups']),
            ])
            ->add('asSpeaker', CheckboxType::class, [
                'required' => false,
            ])
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
    }
}
