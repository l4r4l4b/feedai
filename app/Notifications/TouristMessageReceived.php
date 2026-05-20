<?php

namespace App\Notifications;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail-only notification to the vendor when a tourist sends a message.
 * The link goes back to the dashboard inbox.
 *
 * Intentionally not queueable in the hackathon — synchronous sending keeps
 * test and demo setup simple. Mail driver is Postmark in production,
 * Mailpit in dev.
 */
class TouristMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public readonly Conversation $conversation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->conversation->tourist_name ?: 'A tourist';
        $url = route('inbox.show', ['conversation' => $this->conversation->token]);

        return (new MailMessage)
            ->subject('New message from '.$name)
            ->greeting('Hi!')
            ->line($name.' just sent you a message.')
            ->action('Open in inbox', $url)
            ->line('Reply directly in the FeedAI dashboard — we translate automatically.');
    }
}
