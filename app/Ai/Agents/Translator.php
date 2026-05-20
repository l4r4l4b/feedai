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
 * Translates a single Markdown content file (frontmatter + body) into a
 * target locale. Input and output are the full file as a string — the agent
 * preserves YAML structure and only rewrites human-language values.
 *
 * Output is intentionally NOT structured (no JSON schema): keeping the AI
 * call shaped like text-in/text-out is the most robust way to preserve the
 * exact frontmatter format that the public feed renderer already consumes.
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxTokens(2048)]
#[Temperature(0.1)]
class Translator implements Agent
{
    use Promptable;

    private const LOCALE_NAMES = [
        'en' => 'English',
        'de' => 'German',
        'th' => 'Thai',
    ];

    public function __construct(public string $targetLocale) {}

    public function instructions(): Stringable|string
    {
        $name = self::LOCALE_NAMES[$this->targetLocale] ?? $this->targetLocale;

        return <<<PROMPT
            You translate vendor profile content for a tourist-facing platform.

            The input is a Markdown file with YAML frontmatter:
            ---
            key: value
            ---
            optional body text

            Rules:
            - Translate human-readable text into {$name} ({$this->targetLocale}).
            - Preserve the YAML frontmatter structure EXACTLY: same keys, same order, opening and closing `---` lines.
            - Translate ONLY values that contain natural language. Leave unchanged: URLs, file paths, identifiers, ISO codes, prices, time formats, numbers, emojis, and city / brand names that are commonly kept in English.
            - Keep markdown formatting (lists, links, bold, italics) intact.
            - Do NOT add explanations, comments, or text outside the file content.
            - Do NOT wrap the response in code fences.
            - Output ONLY the translated file content, starting at the opening `---` line.
            PROMPT;
    }
}
