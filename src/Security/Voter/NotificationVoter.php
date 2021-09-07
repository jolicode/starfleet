<?php

namespace App\Security\Voter;

use App\Entity\Notifications\Notification;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NotificationVoter extends Voter
{
    protected function supports(string $attribute, $subject)
    {
        return \in_array($attribute, ['READ_NOTIFICATION'])
            && $subject instanceof Notification;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($subject->getTargetUser() === $user) {
            return true;
        }

        return false;
    }
}
