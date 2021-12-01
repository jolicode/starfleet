<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Factory\Notifications;

use App\Entity\Notifications\AbstractNotification;
use App\Entity\Notifications\ParticipationStatusChangedNotification;
use App\Factory\ParticipationFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static                                       ParticipationStatusChangedNotification|Proxy createOne(array $attributes = [])
 * @method static                                       ParticipationStatusChangedNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static                                       ParticipationStatusChangedNotification|Proxy find($criteria)
 * @method static                                       ParticipationStatusChangedNotification|Proxy findOrCreate(array $attributes)
 * @method static                                       ParticipationStatusChangedNotification|Proxy first(string $sortedField = 'id')
 * @method static                                       ParticipationStatusChangedNotification|Proxy last(string $sortedField = 'id')
 * @method static                                       ParticipationStatusChangedNotification|Proxy random(array $attributes = [])
 * @method static                                       ParticipationStatusChangedNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static                                       ParticipationStatusChangedNotification[]|Proxy[] all()
 * @method static                                       ParticipationStatusChangedNotification[]|Proxy[] findBy(array $attributes)
 * @method static                                       ParticipationStatusChangedNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static                                       ParticipationStatusChangedNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method ParticipationStatusChangedNotification|Proxy create($attributes = [])
 */
final class ParticipationStatusChangedNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => AbstractNotification::TRIGGER_PARTICIPATION_STATUS_CHANGED,
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $participation = ParticipationFactory::findOrCreate([
                    'participant' => $attributes['targetUser'],
                ]);

                return new ParticipationStatusChangedNotification(
                    $participation->object(),
                    $attributes['emitter'],
                    $attributes['targetUser'],
                    $attributes['trigger']
                );
            })
        ;
    }

    protected static function getClass(): string
    {
        return ParticipationStatusChangedNotification::class;
    }
}
