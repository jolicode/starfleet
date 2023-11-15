<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MustBeByUserValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * @param Collection   $value
     * @param MustBeByUser $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value->isEmpty()) {
            return;
        }

        if (!$value->contains($this->security->getUser())) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
