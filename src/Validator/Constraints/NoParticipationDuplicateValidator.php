<?php

namespace App\Validator\Constraints;

use App\Entity\Participation;
use App\Repository\ParticipationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoParticipationDuplicateValidator extends ConstraintValidator
{
    public function __construct(
        private ParticipationRepository $participationRepository,
    ) {
    }

    /**
     * @param Participation|null       $participation
     * @param NoParticipationDuplicate $constraint
     */
    public function validate($participation, Constraint $constraint): void
    {
        if (!$participation) {
            return;
        }

        if (!$participation instanceof Participation) {
            throw new UnexpectedValueException($participation, Participation::class);
        }

        $existingParticipation = $this->participationRepository->findOneBy([
            'participant' => $participation->getParticipant(),
            'conference' => $participation->getConference(),
        ]);

        if ($existingParticipation) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ user_name }}', $participation->getParticipant()->getName())
                ->addViolation()
            ;
        }
    }
}
