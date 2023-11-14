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

use App\Entity\Conference;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEndedCfpValidator extends ConstraintValidator
{
    /**
     * @param Conference  $conference
     * @param NotEndedCfp $constraint
     */
    public function validate($conference, Constraint $constraint): void
    {
        if (!$conference instanceof Conference) {
            return;
        }

        if (!$conference->getCfpEndAt()) {
            return;
        }

        if ($conference->getCfpEndAt() < new \DateTime()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ conference_name }}', $conference->getName())
                ->addViolation()
            ;
        }
    }
}
