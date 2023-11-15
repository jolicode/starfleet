<?php

namespace App\Validator\Constraints;

use App\Entity\Conference;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEndedConferenceValidator extends ConstraintValidator
{
    /**
     * @param Conference|null    $conference
     * @param NotEndedConference $constraint
     */
    public function validate($conference, Constraint $constraint): void
    {
        if (!$conference) {
            return;
        }

        if ($conference->getStartAt() < new \DateTime()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ conference_name }}', $conference->getName())
                ->addViolation()
            ;
        }
    }
}
