<?php

namespace App\Factory\Notifications;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Proxy;
use App\Factory\SubmitFactory;
use Zenstruck\Foundry\ModelFactory;
use App\Entity\Notifications\Notification;
use App\Entity\Notifications\SubmitStatusChangedNotification;

/**
 * @method static SubmitStatusChangedNotification|Proxy createOne(array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static SubmitStatusChangedNotification|Proxy find($criteria)
 * @method static SubmitStatusChangedNotification|Proxy findOrCreate(array $attributes)
 * @method static SubmitStatusChangedNotification|Proxy first(string $sortedField = 'id')
 * @method static SubmitStatusChangedNotification|Proxy last(string $sortedField = 'id')
 * @method static SubmitStatusChangedNotification|Proxy random(array $attributes = [])
 * @method static SubmitStatusChangedNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] all()
 * @method static SubmitStatusChangedNotification[]|Proxy[] findBy(array $attributes)
 * @method static SubmitStatusChangedNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method SubmitStatusChangedNotification|Proxy create($attributes = [])
 */
final class SubmitStatusChangedNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => Notification::TRIGGER_SUBMIT_STATUS_CHANGED,
            'submit' => SubmitFactory::random(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $notification =  new SubmitStatusChangedNotification($attributes['submit'], $attributes['targetUser']);
                $notification->setTrigger($attributes['trigger']);
                $notification->setEmitter($attributes['emitter']);

                return $notification;
            })
        ;
    }

    protected static function getClass(): string
    {
        return SubmitStatusChangedNotification::class;
    }
}
