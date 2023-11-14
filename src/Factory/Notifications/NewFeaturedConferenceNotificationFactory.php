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
use App\Entity\Notifications\NewFeaturedConferenceNotification;
use App\Factory\ConferenceFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static NewFeaturedConferenceNotification|Proxy     createOne(array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static NewFeaturedConferenceNotification|Proxy     find($criteria)
 * @method static NewFeaturedConferenceNotification|Proxy     findOrCreate(array $attributes)
 * @method static NewFeaturedConferenceNotification|Proxy     first(string $sortedField = 'id')
 * @method static NewFeaturedConferenceNotification|Proxy     last(string $sortedField = 'id')
 * @method static NewFeaturedConferenceNotification|Proxy     random(array $attributes = [])
 * @method static NewFeaturedConferenceNotification|Proxy     randomOrCreate(array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] all()
 * @method static NewFeaturedConferenceNotification[]|Proxy[] findBy(array $attributes)
 * @method static NewFeaturedConferenceNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method        NewFeaturedConferenceNotification|Proxy     create($attributes = [])
 */
final class NewFeaturedConferenceNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'trigger' => AbstractNotification::TRIGGER_NEW_FEATURED_CONFERENCE,
            'conference' => ConferenceFactory::random(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                return new NewFeaturedConferenceNotification($attributes['conference'], $attributes['targetUser'], $attributes['trigger']);
            })
        ;
    }

    protected static function getClass(): string
    {
        return NewFeaturedConferenceNotification::class;
    }
}
