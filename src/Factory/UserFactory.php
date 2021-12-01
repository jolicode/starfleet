<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static     User|Proxy createOne(array $attributes = [])
 * @method static     User[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static     User|Proxy find($criteria)
 * @method static     User|Proxy findOrCreate(array $attributes)
 * @method static     User|Proxy first(string $sortedField = 'id')
 * @method static     User|Proxy last(string $sortedField = 'id')
 * @method static     User|Proxy random(array $attributes = [])
 * @method static     User|Proxy randomOrCreate(array $attributes = [])
 * @method static     User[]|Proxy[] all()
 * @method static     User[]|Proxy[] findBy(array $attributes)
 * @method static     User[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static     User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method Proxy|User create($attributes = [])
 */
final class UserFactory extends ModelFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->name(),
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'password',
            'job' => self::faker()->jobTitle(),
            'twitterAccount' => '@' . self::faker()->word(),
            'tshirtSize' => self::faker()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'bio' => self::faker()->paragraph(),
            'foodPreferences' => self::faker()->paragraph(),
            'allergies' => self::faker()->paragraph(),
        ];
    }

    protected function initialize(): self
    {
        return $this->afterInstantiate(function (User $user) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        });
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
