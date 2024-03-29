<?php

namespace App\Security\Voter;

use App\Entity\Submit;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccountSubmitVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['SUBMIT_ACTION'])
            && $subject instanceof Submit;
    }

    /** @param Submit $subject */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->getUsers()->contains($user)) {
            return true;
        }

        return false;
    }
}
