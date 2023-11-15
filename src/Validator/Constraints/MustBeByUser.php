<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class MustBeByUser extends Constraint
{
    public string $message = 'You must include yourself.';
}
