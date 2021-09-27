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

use App\Entity\Participation;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static              Participation|Proxy createOne(array $attributes = [])
 * @method static              Participation[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static              Participation|Proxy find($criteria)
 * @method static              Participation|Proxy findOrCreate(array $attributes)
 * @method static              Participation|Proxy first(string $sortedField = 'id')
 * @method static              Participation|Proxy last(string $sortedField = 'id')
 * @method static              Participation|Proxy random(array $attributes = [])
 * @method static              Participation|Proxy randomOrCreate(array $attributes = [])
 * @method static              Participation[]|Proxy[] all()
 * @method static              Participation[]|Proxy[] findBy(array $attributes)
 * @method static              Participation[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static              Participation[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method Participation|Proxy create($attributes = [])
 */
final class ParticipationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'conference' => ConferenceFactory::random(),
            'participant' => UserFactory::random(),
            'asSpeaker' => $asSpeaker = self::faker()->boolean(40),
            'transportStatus' => !$asSpeaker && self::faker()->boolean(75) ? Participation::STATUS_NEEDED : Participation::STATUS_NOT_NEEDED,
            'hotelStatus' => !$asSpeaker && self::faker()->boolean(75) ? Participation::STATUS_NEEDED : Participation::STATUS_NOT_NEEDED,
            'conferenceTicketStatus' => $asSpeaker ? Participation::STATUS_NOT_NEEDED : Participation::STATUS_NEEDED,
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Participation::class;
    }
}
