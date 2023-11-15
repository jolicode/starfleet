<?php

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

        $requestedConference = null;

        foreach ($this->conferenceRepository->findBy(['name' => $conferenceName]) as $conference) {
            $requestedConference = $requestedConference ?: $conference;

            if ($requestedConference->getStartAt() < $conference->getStartAt()) {
                $requestedConference = $conference;
            }
        }

        if (!$requestedConference) {
            throw new TransformationFailedException(sprintf('The conference with name "%s" doesn\'t seem to exist.', $conferenceName));
        }

        return $requestedConference;
    }
}
