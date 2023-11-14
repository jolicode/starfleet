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
use App\Entity\Notifications\SubmitCancelledNotification;
use App\Factory\SubmitFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static SubmitCancelledNotification|Proxy     createOne(array $attributes = [])
 * @method static SubmitCancelledNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static SubmitCancelledNotification|Proxy     find($criteria)
 * @method static SubmitCancelledNotification|Proxy     findOrCreate(array $attributes)
 * @method static SubmitCancelledNotification|Proxy     first(string $sortedField = 'id')
 * @method static SubmitCancelledNotification|Proxy     last(string $sortedField = 'id')
 * @method static SubmitCancelledNotification|Proxy     random(array $attributes = [])
 * @method static SubmitCancelledNotification|Proxy     randomOrCreate(array $attributes = [])
 * @method static SubmitCancelledNotification[]|Proxy[] all()
 * @method static SubmitCancelledNotification[]|Proxy[] findBy(array $attributes)
 * @method static SubmitCancelledNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static SubmitCancelledNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method        Proxy|SubmitCancelledNotification     create($attributes = [])
 */
final class SubmitCancelledNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'emitter' => UserFactory::random(),
            'trigger' => AbstractNotification::TRIGGER_SUBMIT_CANCELLED,
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

                return new SubmitCancelledNotification(
                    $submit->getTalk(),
                    $submit->getConference(),
                    $attributes['emitter'],
                    $attributes['targetUser'],
                    $attributes['trigger']
                );
            })
        ;
    }

    protected static function getClass(): string
    {
        return SubmitCancelledNotification::class;
    }
}
