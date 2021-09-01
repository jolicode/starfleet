<?php

namespace App\Factory;

use App\Entity\Submit;
use App\Entity\Conference;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Proxy;
use App\Factory\SubmitFactory;
use App\Factory\ConferenceFactory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\RepositoryProxy;
use App\Entity\Notifications\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @method static Notification|Proxy createOne(array $attributes = [])
 * @method static Notification[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static Notification|Proxy find($criteria)
 * @method static Notification|Proxy findOrCreate(array $attributes)
 * @method static Notification|Proxy first(string $sortedField = 'id')
 * @method static Notification|Proxy last(string $sortedField = 'id')
 * @method static Notification|Proxy random(array $attributes = [])
 * @method static Notification|Proxy randomOrCreate(array $attributes = [])
 * @method static Notification[]|Proxy[] all()
 * @method static Notification[]|Proxy[] findBy(array $attributes)
 * @method static Notification[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Notification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static NotificationRepository|RepositoryProxy repository()
 * @method Notification|Proxy create($attributes = [])
 */
final class NotificationFactory extends ModelFactory
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    protected function getDefaults(): array
    {
        return match (random_int(1, 4)) {
            1 => $this->newFeaturedConferenceNotification(),
            2 => $this->submitStatusChangedNotification(),
            3 => $this->participationStatusChangedNotification(),
            4 => $this->submitAddedNotification(),
        };
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return Notification::class;
    }

    private function newFeaturedConferenceNotification(): array
    {
        return [
            'targetUser' => UserFactory::randomOrCreate(),
            'createdAt' => self::faker()->dateTimeThisMonth(),
            'trigger' => Notification::TRIGGER_NEW_FEATURED_CONFERENCE,
            'data' => [
                'objects' => [
                    Conference::class => $this->serializer->serialize(ConferenceFactory::randomOrCreate(), 'json'),
                ],
            ],
        ];
    }

    private function submitStatusChangedNotification(): array
    {
        return [
            'targetUser' => UserFactory::randomOrCreate(),
            'createdAt' => self::faker()->dateTimeThisMonth(),
            'trigger' => Notification::TRIGGER_SUBMIT_STATUS_CHANGED,
            'data' => [
                'objects' => [
                    Submit::class => $this->serializer->serialize(SubmitFactory::findOrCreate(), 'json')
                ],
                'strings' => [
                    'emitter' => UserFactory::findOrCreate()->getName(),
                ]
            ]
        ];
    }

    private function participationStatusChangedNotification(): array
    {
        return [
            'targetUser' => UserFactory::randomOrCreate(),
            'createdAt' => self::faker()->dateTimeThisMonth(),
            'trigger' => Notification::TRIGGER_PARTICIPATION_STATUS_CHANGED,
            'data' => [
                'objects' => [
                    Participation::class => $this->serializer->serialize(ParticipationFactory::findOrCreate(), 'json')
                ],
                'strings' => [
                    'emitter' => UserFactory::findOrCreate()->getName(),
                ]
            ]
        ];
    }

    private function submitAddedNotification(): array
    {
        return [
            'targetUser' => UserFactory::randomOrCreate(),
            'createdAt' => self::faker()->dateTimeThisMonth(),
            'trigger' => Notification::TRIGGER_SUBMIT_ADDED,
            'data' => [
                'objects' => [
                    Submit::class => $this->serializer->serialize(SubmitFactory::findOrCreate(), 'json')
                ]
            ]
        ];
    }
}
