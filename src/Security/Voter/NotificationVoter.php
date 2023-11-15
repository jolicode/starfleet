<?php

namespace App\Security\Voter;

use App\Entity\Notifications\AbstractNotification;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NotificationVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['NOTIFICATION_READ'])
            && $subject instanceof AbstractNotification;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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
