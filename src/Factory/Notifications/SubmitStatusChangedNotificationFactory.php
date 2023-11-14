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
use App\Entity\Notifications\SubmitStatusChangedNotification;
use App\Factory\SubmitFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static SubmitStatusChangedNotification|Proxy     createOne(array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static SubmitStatusChangedNotification|Proxy     find($criteria)
 * @method static SubmitStatusChangedNotification|Proxy     findOrCreate(array $attributes)
 * @method static SubmitStatusChangedNotification|Proxy     first(string $sortedField = 'id')
 * @method static SubmitStatusChangedNotification|Proxy     last(string $sortedField = 'id')
 * @method static SubmitStatusChangedNotification|Proxy     random(array $attributes = [])
 * @method static SubmitStatusChangedNotification|Proxy     randomOrCreate(array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] all()
 * @method static SubmitStatusChangedNotification[]|Proxy[] findBy(array $attributes)
 * @method static SubmitStatusChangedNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static SubmitStatusChangedNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method        Proxy|SubmitStatusChangedNotification     create($attributes = [])
 */
final class SubmitStatusChangedNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => AbstractNotification::TRIGGER_SUBMIT_STATUS_CHANGED,
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

                return new SubmitStatusChangedNotification(
                    $submit->object(),
                    $attributes['emitter'],
                    $attributes['targetUser'],
                    $attributes['trigger']
                );
            })
        ;
    }

    protected static function getClass(): string
    {
        return SubmitStatusChangedNotification::class;
    }
}
