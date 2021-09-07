<?php

namespace App\Twig;

use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationsFilter extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getUnreadNotifications', [$this, 'getUnread']),
        ];
    }

    public function getUnread(Collection $notifications)
    {
        $unread = [];

        foreach ($notifications as $notification) {
            if (!$notification->isRead()) {
                $unread[] = $notification;
            }
        }

        return $unread;
    }
}
