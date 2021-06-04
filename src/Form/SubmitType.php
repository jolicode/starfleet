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

use App\Entity\Talk;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SubmitType extends AbstractType
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conference', ConferenceDatalistType::class)
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
            ])
            ->add('talk', EntityType::class, [
                'class' => Talk::class,
            ])
        ;
    }
}
