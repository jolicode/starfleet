<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataTransformer;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConferenceNameTransformer implements DataTransformerInterface
{
    public function __construct(
        private ConferenceRepository $conferenceRepository
    ) {
    }

    public function transform($conference): string
    {
        if (null === $conference) {
            return '';
        }

        return $conference->getName();
    }

    public function reverseTransform($conferenceName): ?Conference
    {
        if (!$conferenceName) {
            return null;
        }

        $conference = $this->conferenceRepository->findOneBy(['name' => $conferenceName]);

        if (null === $conference) {
            throw new TransformationFailedException(sprintf('The conference with name "%s" doesn\'t seem to exist.', $conferenceName));
        }

        return $conference;
    }
}