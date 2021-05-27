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

use App\Entity\Continent;
use App\Repository\ContinentRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static          Continent|Proxy createOne(array $attributes = [])
 * @method static          Continent[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static          Continent|Proxy find($criteria)
 * @method static          Continent|Proxy findOrCreate(array $attributes)
 * @method static          Continent|Proxy first(string $sortedField = 'id')
 * @method static          Continent|Proxy last(string $sortedField = 'id')
 * @method static          Continent|Proxy random(array $attributes = [])
 * @method static          Continent|Proxy randomOrCreate(array $attributes = [])
 * @method static          Continent[]|Proxy[] all()
 * @method static          Continent[]|Proxy[] findBy(array $attributes)
 * @method static          Continent[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static          Continent[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          ContinentRepository|RepositoryProxy repository()
 * @method Continent|Proxy create($attributes = [])
 */
final class ContinentFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->unique()->randomElement([
                'Africa',
                'Asia',
                'Europe',
                'North America',
                'Oceania',
                'South America',
            ]),
            'enabled' => true,
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Continent::class;
    }
}
