<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class NotEndedConference extends Constraint
{
    public string $message = 'The "{{ conference_name }}" Conference has already ended.';
}
