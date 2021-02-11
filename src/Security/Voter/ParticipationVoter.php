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

use App\Entity\Participation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipationVoter extends Voter
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['PARTICIPATION_SHOW'])
            && ($subject instanceof Participation);
    }

    /**
     * @param Participation $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());

        if (\in_array('PARTICIPATION_SHOW', $roles)) {
            return true;
        }

        if ($user === $subject->getParticipant()) {
            return true;
        }

        return false;
    }
}
