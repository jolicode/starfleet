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

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static           Conference|Proxy createOne(array $attributes = [])
 * @method static           Conference[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static           Conference|Proxy find($criteria)
 * @method static           Conference|Proxy findOrCreate(array $attributes)
 * @method static           Conference|Proxy first(string $sortedField = 'id')
 * @method static           Conference|Proxy last(string $sortedField = 'id')
 * @method static           Conference|Proxy random(array $attributes = [])
 * @method static           Conference|Proxy randomOrCreate(array $attributes = [])
 * @method static           Conference[]|Proxy[] all()
 * @method static           Conference[]|Proxy[] findBy(array $attributes)
 * @method static           Conference[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static           Conference[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static           ConferenceRepository|RepositoryProxy repository()
 * @method Conference|Proxy create($attributes = [])
 */
final class ConferenceFactory extends ModelFactory
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        $startDate = self::faker()->dateTimeBetween('-3 years', '+1 year');
        $endDate = clone $startDate;
        $cfpEndDate = clone $startDate;

        return [
            'name' => $name = self::faker()->words(3, true),
            'slug' => $this->slugger->slug($name),
            'description' => self::faker()->paragraph(),
            'startAt' => $startDate,
            'endAt' => $endDate->modify('+' . self::faker()->numberBetween(0, 3) . 'days'),
            'cfpUrl' => self::faker()->url(),
            'cfpEndAt' => $cfpEndDate->modify('-' . self::faker()->numberBetween(30, 90) . 'days'),
            'siteUrl' => self::faker()->url(),
            'articleUrl' => self::faker()->url(),
            'country' => strtoupper(self::faker()->countryCode()),
            'city' => self::faker()->city(),
            'coordinates' => [self::faker()->longitude(), self::faker()->latitude(min: -85.0511, max: 85.0511)],
            'tags' => self::faker()->randomElements(self::faker()->words(10), self::faker()->numberBetween(1, 3)),
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Conference::class;
    }
}
