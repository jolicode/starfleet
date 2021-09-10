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

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class NoSubmitDuplicate extends Constraint
{
    public string $message = 'A submit for {{ conference }} is already registered with the same talk and the same users.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
