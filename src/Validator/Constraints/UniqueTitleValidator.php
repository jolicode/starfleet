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

use App\Repository\TalkRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTitleValidator extends ConstraintValidator
{
    public function __construct(
        private TalkRepository $talkRepository,
    ) {
    }

    /**
     * @param string      $formTitle
     * @param UniqueTitle $constraint
     */
    public function validate($formTitle, Constraint $constraint): void
    {
        if (!$formTitle) {
            return;
        }

        $contextTalk = $this->context->getObject();
        $existingTalk = $this->talkRepository->findOneBy(['title' => $contextTalk->getTitle()]);

        if ($existingTalk && $existingTalk !== $contextTalk) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
