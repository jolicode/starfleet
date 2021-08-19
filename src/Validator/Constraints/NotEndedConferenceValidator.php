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
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEndedConferenceValidator extends ConstraintValidator
{
    /** @var Conference */
    public function validate($conference, Constraint $constraint): void
    {
        if (!$conference) {
            return;
        }

        if ($conference->getStartAt() < new DateTime()) {
            // @phpstan-ignore-next-line
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ conference_name }}', $conference->getName())
                ->addViolation()
            ;
        }
    }
}
