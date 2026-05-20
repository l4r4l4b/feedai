<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Befüllt den Demo-Vendor-Posteingang mit ein paar realistischen
 * Tourist-Konversationen, damit Inbox + Admin-ConversationList direkt
 * etwas zu zeigen haben. Idempotent: Kontrolle per token, der aus
 * vorhersehbaren Werten generiert wird.
 */
class DemoInboxSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::where('slug', 'demo')->first();

        if (! $vendor) {
            return;
        }

        $this->seedConversation(
            vendor: $vendor,
            token: str_pad('lenatomde', 48, 'a'),
            touristLocale: 'de',
            touristName: 'Lena & Tom',
            touristEmail: 'lena@example.test',
            messages: [
                [
                    'sender' => 'tourist',
                    'original_text' => 'Hallo Mae Som! Habt ihr heute Abend ab 19 Uhr noch Platz für zwei?',
                    'original_locale' => 'de',
                    'translated_text' => 'สวัสดี Mae Som! คืนนี้ตั้งแต่ 19:00 น. ยังมีที่นั่งสำหรับสองคนไหม?',
                    'translated_locale' => 'th',
                    'minutes_ago' => 90,
                ],
                [
                    'sender' => 'vendor',
                    'original_text' => 'สวัสดีครับ! แวะมาได้เลยครับ ไม่ต้องจอง — เปิด 17:00–23:00',
                    'original_locale' => 'th',
                    'translated_text' => 'Hallo! Kommt einfach vorbei — keine Reservierung nötig. Wir öffnen 17–23 Uhr.',
                    'translated_locale' => 'de',
                    'minutes_ago' => 75,
                ],
                [
                    'sender' => 'tourist',
                    'original_text' => 'Super, danke! Bis später.',
                    'original_locale' => 'de',
                    'translated_text' => 'ดีมาก ขอบคุณครับ! เจอกัน',
                    'translated_locale' => 'th',
                    'minutes_ago' => 60,
                ],
            ],
        );

        $this->seedConversation(
            vendor: $vendor,
            token: str_pad('saraen', 48, 'b'),
            touristLocale: 'en',
            touristName: 'Sara',
            touristEmail: null,
            messages: [
                [
                    'sender' => 'tourist',
                    'original_text' => 'Hi! Do you have vegan Pad Thai? No fish sauce please.',
                    'original_locale' => 'en',
                    'translated_text' => 'สวัสดี! มีผัดไทยวีแกนไหมคะ ไม่ใส่น้ำปลาค่ะ',
                    'translated_locale' => 'th',
                    'minutes_ago' => 30,
                ],
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    private function seedConversation(
        Vendor $vendor,
        string $token,
        string $touristLocale,
        string $touristName,
        ?string $touristEmail,
        array $messages,
    ): void {
        $conversation = Conversation::updateOrCreate(
            ['token' => $token],
            [
                'vendor_id' => $vendor->id,
                'tourist_locale' => $touristLocale,
                'tourist_name' => $touristName,
                'tourist_email' => $touristEmail,
                'last_message_at' => now()->subMinutes((int) ($messages[array_key_last($messages)]['minutes_ago'] ?? 0)),
            ],
        );

        // Nur seeden falls noch keine Messages dranhängen — vermeidet Duplikate
        // bei Re-Seeds und respektiert manuelle Bearbeitung.
        if ($conversation->messages()->exists()) {
            return;
        }

        foreach ($messages as $payload) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender' => $payload['sender'],
                'original_text' => $payload['original_text'],
                'original_locale' => $payload['original_locale'],
                'translated_text' => $payload['translated_text'],
                'translated_locale' => $payload['translated_locale'],
                'read_at' => $payload['sender'] === 'tourist' ? now()->subMinutes((int) $payload['minutes_ago'] - 5) : null,
                'created_at' => now()->subMinutes((int) $payload['minutes_ago']),
                'updated_at' => now()->subMinutes((int) $payload['minutes_ago']),
            ]);
        }
    }
}
