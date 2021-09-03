<?php

namespace App\Factory\Notifications;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\ModelFactory;
use App\Entity\Notifications\Notification;
use App\Entity\Notifications\ParticipationStatusChangedNotification;
use App\Factory\ParticipationFactory;

/**
 * @method static ParticipationStatusChangedNotification|Proxy createOne(array $attributes = [])
 * @method static ParticipationStatusChangedNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static ParticipationStatusChangedNotification|Proxy find($criteria)
 * @method static ParticipationStatusChangedNotification|Proxy findOrCreate(array $attributes)
 * @method static ParticipationStatusChangedNotification|Proxy first(string $sortedField = 'id')
 * @method static ParticipationStatusChangedNotification|Proxy last(string $sortedField = 'id')
 * @method static ParticipationStatusChangedNotification|Proxy random(array $attributes = [])
 * @method static ParticipationStatusChangedNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static ParticipationStatusChangedNotification[]|Proxy[] all()
 * @method static ParticipationStatusChangedNotification[]|Proxy[] findBy(array $attributes)
 * @method static ParticipationStatusChangedNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static ParticipationStatusChangedNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method ParticipationStatusChangedNotification|Proxy create($attributes = [])
 */
final class ParticipationStatusChangedNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => Notification::TRIGGER_PARTICIPATION_STATUS_CHANGED,
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $participation = ParticipationFactory::findOrCreate([
                        'participant' => $attributes['targetUser'],
                ]);

                $notification =  new ParticipationStatusChangedNotification($participation->object(), $attributes['targetUser']);
                $notification->setTrigger($attributes['trigger']);
                $notification->setEmitter($attributes['emitter']);

                return $notification;
            })
        ;
    }

    protected static function getClass(): string
    {
        return ParticipationStatusChangedNotification::class;
    }
}
