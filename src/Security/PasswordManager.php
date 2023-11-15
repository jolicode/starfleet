<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordManager
{
    private PasswordHasherInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasherFactory)
    {
        $this->hasher = $hasherFactory->getPasswordHasher(User::class);
    }

    public function isPasswordValid(string $hashedPassword, string $plainPassword): bool
    {
        return $this->hasher->verify($hashedPassword, $plainPassword);
    }
}
