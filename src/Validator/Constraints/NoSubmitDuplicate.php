<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class NoSubmitDuplicate extends Constraint
{
    public string $message = 'A submit for {{ conference }} is already registered with the same talk and the same users.';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
