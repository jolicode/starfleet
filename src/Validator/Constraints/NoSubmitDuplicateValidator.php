<?php

namespace App\Validator\Constraints;

use App\Entity\Submit;
use App\Repository\SubmitRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoSubmitDuplicateValidator extends ConstraintValidator
{
    public function __construct(
        private SubmitRepository $submitRepository,
    ) {
    }

    /**
     * @param Submit|null       $value
     * @param NoSubmitDuplicate $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        if (!$value instanceof Submit) {
            throw new UnexpectedValueException($value, Submit::class);
        }

        $qb = $this->submitRepository->createQueryBuilder('s');
        $existingSubmits = $qb
            ->andWhere('s.conference = :conference')
            ->andWhere('s.talk = :talk')
            ->innerJoin('s.users', 'u')
            ->andWhere('u.id IN (:users)')
            ->setParameters([
                'conference' => $value->getConference(),
                'talk' => $value->getTalk(),
                'users' => $value->getUsers(),
            ])
            ->getQuery()
            ->getResult()
        ;

        foreach ($existingSubmits as $existingSubmit) {
            $submitUsers = $value->getUsers()->toArray();
            $existingSubmitUsers = $existingSubmit->getUsers()->toArray();
            sort($submitUsers);
            sort($existingSubmitUsers);

            if ($submitUsers === $existingSubmitUsers) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ conference }}', $value->getConference())
                    ->addViolation()
                ;

                return;
            }
        }
    }
}
