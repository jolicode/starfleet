<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\Submit;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SubmitVoter extends Voter
{
    public function __construct(
        private RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['SUBMIT_EDIT'])
            && $subject instanceof Submit;
    }

    /**
     * @param Submit $submit
     */
    protected function voteOnAttribute(string $attribute, $submit, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());

        if (\in_array('SUBMIT_EDIT', $roles)) {
            return true;
        }

        if ($submit->getUsers()->contains($user)) {
            return true;
        }

        return false;
    }
}
