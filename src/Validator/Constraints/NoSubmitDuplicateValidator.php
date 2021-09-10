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

use App\Entity\Submit;
use App\Repository\SubmitRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoSubmitDuplicateValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private SubmitRepository $submitRepository,
    ) {
    }

    /**
     * @param Submit|null       $submit
     * @param NoSubmitDuplicate $constraint
     */
    public function validate($submit, Constraint $constraint): void
    {
        if (!$submit) {
            return;
        }

        if (!$submit instanceof Submit) {
            throw new UnexpectedValueException($submit, Submit::class);
        }

        $qb = $this->submitRepository->createQueryBuilder('s');
        $existingSubmits = $qb
            ->andWhere('s.conference = :conference')
            ->andWhere('s.talk = :talk')
            ->innerJoin('s.users', 'u')
            ->andWhere('u.id IN (:users)')
            ->setParameters([
                'conference' => $submit->getConference(),
                'talk' => $submit->getTalk(),
                'users' => $submit->getUsers(),
            ])
            ->getQuery()
            ->getResult()
        ;

        foreach ($existingSubmits as $existingSubmit) {
            $submitUsers = $submit->getUsers()->toArray();
            $existingSubmitUsers = $existingSubmit->getUsers()->toArray();
            sort($submitUsers);
            sort($existingSubmitUsers);

            if ($submitUsers === $existingSubmitUsers) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ conference }}', $submit->getConference())
                    ->addViolation()
                ;

                return;
            }
        }
    }
}
