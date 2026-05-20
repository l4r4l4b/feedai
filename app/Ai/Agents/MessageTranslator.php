<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Übersetzt eine einzelne Chat-Nachricht zwischen Tourist- und Vendor-Sprache.
 *
 * Anders als der Translator-Agent (Markdown-Files) arbeitet dieser mit
 * plain text, kürzer, einfacher, ohne Frontmatter-Erhalt. Wird einmal
 * pro Message dispatched.
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxTokens(1024)]
#[Temperature(0.1)]
class MessageTranslator implements Agent
{
    use Promptable;

    private const LOCALE_NAMES = [
        'en' => 'English',
        'de' => 'German',
        'th' => 'Thai',
    ];

    public function __construct(
        public readonly string $sourceLocale,
        public readonly string $targetLocale,
    ) {}

    public function instructions(): Stringable|string
    {
        $sourceName = self::LOCALE_NAMES[$this->sourceLocale] ?? $this->sourceLocale;
        $targetName = self::LOCALE_NAMES[$this->targetLocale] ?? $this->targetLocale;

        return <<<PROMPT
            You translate single chat messages between tourists and street-food / tour-guide vendors.

            Source language: {$sourceName} ({$this->sourceLocale})
            Target language: {$targetName} ({$this->targetLocale})

            Rules:
            - Translate the user message into {$targetName} as naturally as possible.
            - Preserve names, prices, URLs, phone numbers, emojis, and date/time formats.
            - Keep the tone of the original, casual stays casual, polite stays polite.
            - Output ONLY the translated text. No prefix, no explanation, no quotes around it.
            - If the message is already in {$targetName}, return it unchanged.
            PROMPT;
    }
}
