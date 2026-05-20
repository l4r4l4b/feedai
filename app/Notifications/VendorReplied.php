<?php

namespace App\Notifications;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail-Benachrichtigung an den Touristen wenn der Vendor antwortet.
 * Versand nur falls der Tourist beim Contact-Form eine Email angegeben hat.
 * Link führt zur signed-URL-Chat-View, kein Login erforderlich.
 */
class VendorReplied extends Notification
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
        $vendorName = $this->conversation->vendor->name;
        $url = route('conversations.show', ['conversation' => $this->conversation->token]);

        return (new MailMessage)
            ->subject($vendorName.' hat dir geantwortet')
            ->greeting('Hi!')
            ->line($vendorName.' hat auf deine Nachricht geantwortet.')
            ->action('Antwort lesen', $url)
            ->line('Du kannst direkt antworten — wir übersetzen automatisch.');
    }
}
