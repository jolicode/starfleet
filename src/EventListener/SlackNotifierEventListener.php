<?php

namespace App\EventListener;

use App\Entity\Submit;
use App\Event\DailyNotificationEvent;
use App\Event\NewTalkSubmittedEvent;
use App\Event\Notification\SubmitStatusChangedEvent;
use App\Notifiers\Slack\SlackNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlackNotifierEventListener implements EventSubscriberInterface
{
    private bool $disabled = false;

    public function __construct(
        private SlackNotifier $slackNotifier,
        string $env,
    ) {
        if ('test' === $env) {
            $this->disable();
        }
    }

    /**
     * Disable the listener, useful while loading fixtures for example.
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /** @return array<string,string> */
    public static function getSubscribedEvents(): array
    {
        return [
            NewTalkSubmittedEvent::class => 'onNewTalkSubmitted',
            DailyNotificationEvent::class => 'dailyNotification',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
        ];
    }

    public function onNewTalkSubmitted(NewTalkSubmittedEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $this->slackNotifier->sendNewTalkSubmittedNotification($event->getSubmits(), $event->getTalk());
    }

    public function dailyNotification(DailyNotificationEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $this->slackNotifier->sendDailyNotification($event->getNewConferences(), $event->getEndingCfps());
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event): void
    {
        if ($this->disabled) {
            return;
        }

        $status = $event->getSubmit()->getStatus();

        if (Submit::STATUS_PENDING === $status || Submit::STATUS_DONE === $status) {
            return;
        }

        $this->slackNotifier->sendSubmitStatusChangedNotification($event->getSubmit());
    }
}
