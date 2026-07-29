<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Global inbox notification (Laravel database channel).
 * Shared across every panel — one inbox per user.
 *
 * Toast flash messages still use App\AdminPanel\Notifications\Notify.
 */
class AdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public ?string $title = null,
        public string $type = 'info',
        public ?string $url = null,
    ) {
        $allowed = ['success', 'info', 'warning', 'danger'];
        $this->type = in_array($type, $allowed, true) ? $type : 'info';
    }

    /**
     * @param  \Illuminate\Notifications\Notifiable|object  $notifiable
     */
    public static function send(
        object $notifiable,
        string $message,
        ?string $title = null,
        string $type = 'info',
        ?string $url = null,
    ): void {
        $notifiable->notify(new static($message, $title, $type, $url));
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: ?string, message: string, type: string, url: ?string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'url' => $this->url,
        ];
    }
}
