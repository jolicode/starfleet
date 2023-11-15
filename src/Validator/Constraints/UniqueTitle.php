<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class UniqueTitle extends Constraint
{
    public string $message = 'This title already exists. Please choose another one.';
}
