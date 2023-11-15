<?php

namespace App\Security\Voter;

use App\Entity\Participation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccountParticipationVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['PARTICIPATION_ACTION'])
            && $subject instanceof Participation;
    }

    /** @param Participation $subject */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user === $subject->getParticipant()) {
            return true;
        }

        return false;
    }
}
