<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

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
     * @param mixed        $value
     * @param MustBeByUser $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!\count($value)) {
            return;
        }

        $hasViolation = false;

        if ($value instanceof Collection && !$value->contains($this->security->getUser())) {
            $hasViolation = true;
        } elseif (!$value instanceof Collection && !\in_array($this->security->getUser(), $value)) {
            $hasViolation = true;
        }

        if ($hasViolation) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
