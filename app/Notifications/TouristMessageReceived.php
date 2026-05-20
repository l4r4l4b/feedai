<?php

namespace App\Notifications;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail-only Benachrichtigung an den Vendor wenn ein Tourist eine Nachricht
 * schickt. Link führt zurück ins Dashboard-Inbox.
 *
 * Bewusst nicht queueable im Hackathon — synchroner Versand vereinfacht das
 * Test- und Demo-Setup. Mail-Driver ist Postmark in Production, Mailpit im Dev.
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
        $name = $this->conversation->tourist_name ?: 'Ein Tourist';
        $url = route('inbox.show', ['conversation' => $this->conversation->token]);

        return (new MailMessage)
            ->subject('Neue Nachricht von '.$name)
            ->greeting('Hallo!')
            ->line($name.' hat dir gerade eine Nachricht geschickt.')
            ->action('Im Posteingang öffnen', $url)
            ->line('Antworte direkt im FeedAI-Dashboard — wir übersetzen automatisch.');
    }
}
