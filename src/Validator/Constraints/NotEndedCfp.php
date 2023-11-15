<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class NotEndedCfp extends Constraint
{
    public string $message = 'The CFP for "{{ conference_name }}" has already ended.';
}
