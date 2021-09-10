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
use App\Entity\Notifications\NewSubmitNotification;
use App\Factory\SubmitFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static                      NewSubmitNotification|Proxy createOne(array $attributes = [])
 * @method static                      NewSubmitNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static                      NewSubmitNotification|Proxy find($criteria)
 * @method static                      NewSubmitNotification|Proxy findOrCreate(array $attributes)
 * @method static                      NewSubmitNotification|Proxy first(string $sortedField = 'id')
 * @method static                      NewSubmitNotification|Proxy last(string $sortedField = 'id')
 * @method static                      NewSubmitNotification|Proxy random(array $attributes = [])
 * @method static                      NewSubmitNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static                      NewSubmitNotification[]|Proxy[] all()
 * @method static                      NewSubmitNotification[]|Proxy[] findBy(array $attributes)
 * @method static                      NewSubmitNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static                      NewSubmitNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method NewSubmitNotification|Proxy create($attributes = [])
 */
final class NewSubmitNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => AbstractNotification::TRIGGER_NEW_SUBMIT,
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
                    ],
                ]);

                $notification = new NewSubmitNotification(
                    $submit->object(),
                    $attributes['emitter'],
                    $attributes['targetUser'],
                    $attributes['trigger']
                );

                return $notification;
            })
        ;
    }

    protected static function getClass(): string
    {
        return NewSubmitNotification::class;
    }
}
