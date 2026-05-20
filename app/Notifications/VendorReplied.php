<?php

namespace App\Notifications;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail notification to the tourist when the vendor replies. Only sent if
 * the tourist provided an email on the contact form. The link goes to the
 * token-based chat view — no login required.
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
            ->subject($vendorName.' replied to you')
            ->greeting('Hi!')
            ->line($vendorName.' answered your message.')
            ->action('Read reply', $url)
            ->line('You can reply right back — we translate automatically.');
    }
}
