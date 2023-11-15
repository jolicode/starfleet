<?php

namespace App\Factory;

use App\Entity\Submit;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Submit|Proxy     createOne(array $attributes = [])
 * @method static Submit[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static Submit|Proxy     find($criteria)
 * @method static Submit|Proxy     findOrCreate(array $attributes)
 * @method static Submit|Proxy     first(string $sortedField = 'id')
 * @method static Submit|Proxy     last(string $sortedField = 'id')
 * @method static Submit|Proxy     random(array $attributes = [])
 * @method static Submit|Proxy     randomOrCreate(array $attributes = [])
 * @method static Submit[]|Proxy[] all()
 * @method static Submit[]|Proxy[] findBy(array $attributes)
 * @method static Submit[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Submit[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method        Proxy|Submit     create($attributes = [])
 */
final class SubmitFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'submittedAt' => self::faker()->dateTimeBetween('-2years'),
            'status' => self::faker()->randomElement(Submit::STATUSES),
            'users' => $users = UserFactory::randomRange(1, 2),
            'talk' => TalkFactory::random(),
            'conference' => ConferenceFactory::random(),
            'submittedBy' => $users[0],
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Submit::class;
    }
}
