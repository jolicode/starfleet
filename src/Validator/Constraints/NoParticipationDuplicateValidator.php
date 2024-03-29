<?php

namespace App\Validator\Constraints;

use App\Entity\Conference;
use App\Entity\User;
use App\Repository\ParticipationRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoParticipationDuplicateValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private ParticipationRepository $participationRepository,
    ) {
    }

    /**
     * @param Conference|null          $conference
     * @param NoParticipationDuplicate $constraint
     */
    public function validate($conference, Constraint $constraint): void
    {
        if (!$conference) {
            return;
        }

        if (!$conference instanceof Conference) {
            throw new UnexpectedValueException($conference, Conference::class);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $existingParticipation = $this->participationRepository->findOneBy([
            'participant' => $user,
            'conference' => $conference,
        ]);

        if ($existingParticipation) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ user_name }}', $user->getName())
                ->addViolation()
            ;
        }
    }
}
