<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class PasswordManager
{
    private $encoder;

    public function __construct(EncoderFactoryInterface $factory)
    {
        // We will probably always use the same encoder for both User and Client classes
        $this->encoder = $factory->getEncoder(User::class);
    }

    public function encodePassword(string $plainPassword, ?string $salt = null): string
    {
        return $this->encoder->encodePassword($plainPassword, $salt);
    }

    public function isPasswordValid(string $encodedPassword, string $plainPassword, ?string $salt = null): bool
    {
        return $this->encoder->isPasswordValid($encodedPassword, $plainPassword, $salt);
    }

    public function updateLoginablePassword(User $user): void
    {
        $user->setPassword($this->encodePassword($user->getPlainPassword(), $user->getSalt()));
        $user->eraseCredentials();
    }
}
