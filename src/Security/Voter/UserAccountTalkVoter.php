<?php

namespace App\Security\Voter;

use App\Entity\Talk;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccountTalkVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['TALK_ACTION'])
            && $subject instanceof Talk;
    }

    /** @param Talk $subject */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        foreach ($subject->getSubmits() as $submit) {
            if ($submit->getUsers()->contains($user)) {
                return true;
            }
        }

        return false;
    }
}
