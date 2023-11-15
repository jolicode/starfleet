<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class NoParticipationDuplicate extends Constraint
{
    public string $message = 'A participation at this conference is already registered for {{ user_name }}.';
}
