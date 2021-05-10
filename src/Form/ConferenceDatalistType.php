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

use App\Repository\ConferenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ConferenceDatalistType extends AbstractType
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    ) {
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
                    new NotNull(),
                ],
            ]);
    }

    public function getParent(): string
    {
        return DatalistType::class;
    }
}
