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

use App\Entity\Participation;
use App\Repository\ParticipationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;

class ParticipationType extends AbstractType
{
    public function __construct(
        private ParticipationRepository $participationRepository,
        private Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conference', ConferenceDatalistType::class, [
                'disabled' => $options['validation_groups'] && \in_array('edition', $options['validation_groups']) ? true : false,
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
