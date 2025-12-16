<?php

namespace App\Notifications;

use App\Models\AccessKey;
use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public AccessKey $accessKey)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ваш доступ создан')
            ->greeting('Здравствуйте!')
            ->line('Для вас был создан новый ключ доступа.')
            ->line("Название: {$this->accessKey->name}")
            ->line("Ключ: {$this->accessKey->key}")
            ->line('Действует до: ' . $this->accessKey->expires_at->format('d.m.Y'))
            ->line('Если у вас есть вопросы — просто ответьте на это письмо.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
