<?php

namespace App\Factory\Notifications;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\ModelFactory;
use App\Entity\Notifications\Notification;
use App\Entity\Notifications\SubmitAddedNotification;
use App\Factory\SubmitFactory;

/**
 * @method static SubmitAddedNotification|Proxy createOne(array $attributes = [])
 * @method static SubmitAddedNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static SubmitAddedNotification|Proxy find($criteria)
 * @method static SubmitAddedNotification|Proxy findOrCreate(array $attributes)
 * @method static SubmitAddedNotification|Proxy first(string $sortedField = 'id')
 * @method static SubmitAddedNotification|Proxy last(string $sortedField = 'id')
 * @method static SubmitAddedNotification|Proxy random(array $attributes = [])
 * @method static SubmitAddedNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static SubmitAddedNotification[]|Proxy[] all()
 * @method static SubmitAddedNotification[]|Proxy[] findBy(array $attributes)
 * @method static SubmitAddedNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static SubmitAddedNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method SubmitAddedNotification|Proxy create($attributes = [])
 */
final class SubmitAddedNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => Notification::TRIGGER_SUBMIT_ADDED,
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $submit = SubmitFactory::createOne([
                    'users' => [
                        $attributes['targetUser'],
                        UserFactory::createOne(),
                    ]
                ]);

                $notification =  new SubmitAddedNotification($submit->object(), $attributes['targetUser']);
                $notification->setTrigger($attributes['trigger']);
                $notification->setEmitter($attributes['emitter']);

                return $notification;
            })
        ;
    }

    protected static function getClass(): string
    {
        return SubmitAddedNotification::class;
    }
}
