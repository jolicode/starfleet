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

use App\Entity\Talk;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Talk|Proxy     createOne(array $attributes = [])
 * @method static Talk[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static Talk|Proxy     find($criteria)
 * @method static Talk|Proxy     findOrCreate(array $attributes)
 * @method static Talk|Proxy     first(string $sortedField = 'id')
 * @method static Talk|Proxy     last(string $sortedField = 'id')
 * @method static Talk|Proxy     random(array $attributes = [])
 * @method static Talk|Proxy     randomOrCreate(array $attributes = [])
 * @method static Talk[]|Proxy[] all()
 * @method static Talk[]|Proxy[] findBy(array $attributes)
 * @method static Talk[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Talk[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method        Proxy|Talk     create($attributes = [])
 */
final class TalkFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'title' => self::faker()->sentence(),
            'intro' => self::faker()->paragraph(),
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Talk::class;
    }
}
