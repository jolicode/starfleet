<?php

namespace App\Factory\Notifications;

use Zenstruck\Foundry\Proxy;
use App\Factory\ConferenceFactory;
use Zenstruck\Foundry\ModelFactory;
use App\Entity\Notifications\Notification;
use App\Entity\Notifications\NewFeaturedConferenceNotification;

/**
 * @method static NewFeaturedConferenceNotification|Proxy createOne(array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static NewFeaturedConferenceNotification|Proxy find($criteria)
 * @method static NewFeaturedConferenceNotification|Proxy findOrCreate(array $attributes)
 * @method static NewFeaturedConferenceNotification|Proxy first(string $sortedField = 'id')
 * @method static NewFeaturedConferenceNotification|Proxy last(string $sortedField = 'id')
 * @method static NewFeaturedConferenceNotification|Proxy random(array $attributes = [])
 * @method static NewFeaturedConferenceNotification|Proxy randomOrCreate(array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] all()
 * @method static NewFeaturedConferenceNotification[]|Proxy[] findBy(array $attributes)
 * @method static NewFeaturedConferenceNotification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static NewFeaturedConferenceNotification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method NewFeaturedConferenceNotification|Proxy create($attributes = [])
 */
final class NewFeaturedConferenceNotificationFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'trigger' => Notification::TRIGGER_NEW_FEATURED_CONFERENCE,
            'conference' => ConferenceFactory::random(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith(function (array $attributes) {
                $notification =  new NewFeaturedConferenceNotification($attributes['conference'], $attributes['targetUser']);
                $notification->setTrigger($attributes['trigger']);

                return $notification;
            })
        ;
    }

    protected static function getClass(): string
    {
        return NewFeaturedConferenceNotification::class;
    }
}
