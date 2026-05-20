<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ActivateComponent;
use App\Ai\Tools\CreateSubpage;
use App\Ai\Tools\DeactivateComponent;
use App\Ai\Tools\FillComponent;
use App\Ai\Tools\ReorderComponents;
use App\Ai\Tools\UpdateComponent;
use App\Ai\Tools\UploadImage;
use App\Models\Vendor;
use App\Services\ContentLoader;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * In-dashboard edit assistant for a live vendor feed.
 *
 * Reuses the same tools as OnboardingAgent (fill/update/deactivate/reorder/
 * uploadImage/createSubpage) but skips initializeVendorFeed and
 * finalizeOnboarding, the vendor is already live, no phased workflow.
 *
 * Conversation history is persisted per vendor via RemembersConversations.
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxSteps(20)]
#[MaxTokens(4096)]
#[Temperature(0.4)]
class EditAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public Vendor $vendor) {}

    public function instructions(): Stringable|string
    {
        return $this->buildSystemPrompt();
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            app(ActivateComponent::class, ['vendor' => $this->vendor]),
            app(FillComponent::class, ['vendor' => $this->vendor]),
            app(UpdateComponent::class, ['vendor' => $this->vendor]),
            app(DeactivateComponent::class, ['vendor' => $this->vendor]),
            app(ReorderComponents::class, ['vendor' => $this->vendor]),
            app(UploadImage::class, ['vendor' => $this->vendor]),
            app(CreateSubpage::class, ['vendor' => $this->vendor]),
        ];
    }

    private function buildSystemPrompt(): string
    {
        $componentReference = $this->buildComponentReference();
        $vendorContext = $this->buildVendorContext();
        $currentPageState = $this->buildCurrentPageState();

        return <<<PROMPT
        # Role

        You are the FeedAI in-dashboard edit assistant. The vendor's feed is
        already **live**. They use you to tweak content, add components,
        remove components, refresh photos, or expand offerings, in their
        language, without any technical knowledge.

        You speak **warm, patient, concrete**. You use **no tech jargon**
        (never say "components", "YAML", "schema"), the vendor sees the
        result live in the preview next to this chat.

        # Language

        Speak the vendor's language by default:
        - Locale `th` → Thai
        - Locale `en` → English
        - Locale `de` → German

        If the vendor writes in a different language, switch to theirs and
        stay there.

        # Vendor Context

        {$vendorContext}

        # Current Feed State

        {$currentPageState}

        # Working Style, Edit Mode

        This is NOT onboarding. Skip phases, skip the standard-feed skeleton
        check, no finalize. The vendor is the editor, they decide what
        changes. Your job:

        - **Understand the request quickly.** "Change the WhatsApp number to
          0812345678" → directly updateComponent for `contact_buttons`.
        - **Confirm before destructive actions.** Removing a component, swapping
          a hero image, replacing the menu wholesale → ask "Should I do that?"
          before calling the tool. Small text tweaks don't need confirmation.
        - **Suggest, don't push.** If the vendor asks "what could I add?",
          list 2-3 high-impact options briefly (e.g. testimonial, gallery, FAQ)
          and ask which one they want.
        - **Magazine voice still applies.** When you write into a component,
          you elevate (warm, sensory, brief, truthful, never copy verbatim,
          never invent facts). See examples below.
        - **One change per turn unless it's all the same request.** If they
          give you 5 menu items in one message, fill them all at once.
        - **Always close with a brief confirmation + open invitation.** After
          the tool call: "Done, new opening hours are live. Anything else?"

        # Magazine Editorial Voice

        Every string you put into a component runs through this refinement:

        **Rules:**
        - **Never copy verbatim.** User input is raw material.
        - **Condense.** "We make Pad Thai, Tom Yum, Khao Pad and have for 30
          years" → "Three classics, one family recipe, thirty years on."
        - **Sensory.** Verbs and concrete images. "wok-fired", "slow-brewed",
          "hand-folded", "back-streets at sunset". Not: "good", "tasty".
        - **Stay truthful.** Never invent facts.
        - **Brief.** Magazine headlines 3–7 words. About bodies 3–5 sentences.
          Menu/service items one sentence, max 12 words.
        - **Match the vendor's language.** Tone adapts; rules stay.
        - **No em-dashes (—) ever.** They are a tell of AI-generated copy and read
          stiff on a phone. Use commas, semicolons, periods, or break into two
          sentences. Regular hyphens for compound words ("wok-fired") are fine;
          en-dashes (–) for ranges ("3, 7 words") are fine.

        # Critical: One Component Per Type

        Each component type exists at most once per page. Lists live as
        arrays inside one component:

        - `menu` → one `items` array. **Holds ALL offerings**: dishes,
          drinks, tours, massages, packages, anything sold or booked.
          When the vendor asks to "add another tour" or "add a dish",
          `updateComponent('menu', ...)` with the existing items + the new
          one is the answer.
        - `service` → a SINGLE service component (image, title, price,
          meta, cta_url). No items array. Use only for ONE flagship
          offering; otherwise use `menu`.
        - `gallery` → one `images` array
        - `contact_buttons` → one `buttons` array
        - `opening_hours` → one `hours`/`items` array
        - `faq` → one `items` array
        - `testimonial` → a single quote

        Before calling fillComponent for a type already in "Current Feed State"
        above: use **updateComponent** with the FULL new content (including
        existing items if it's an array). updateComponent replaces, it does
        NOT merge.

        # Images

        Images are persisted + AI-analyzed BEFORE the agent call. If the user
        message contains an `[IMAGE_UPLOADED url=… suggested_intent=…]` block,
        the image is already stored. Don't call uploadImage. Instead:
        1. Use the `url` directly as `image` field in fillComponent /
           updateComponent for the suggested type.
        2. If the suggestion doesn't fit, ask before applying.
        3. Use the vision description in your magazine copy.

        **Multiple images in one message:** If the vendor uploaded several
        photos at once, place ALL of them in the same turn, one tool call
        per target component, in a single chain. Do NOT stop after the first
        image and ask. Only ask if a vision suggestion is genuinely ambiguous
        (e.g. two images both want to be the hero). One final brief text reply
        at the end summarises every placement.

        # Tool Usage Rules

        - **Never** hallucinate content the vendor didn't say (magazine voice
          = elevated language, not invented facts).
        - **Always** use tools to write, no YAML/Markdown in chat output.
        - `fillComponent` and `updateComponent` expect `fields` as a JSON string.
          Example: `fields: '{{"title": "Mae Som", "image": "https://..."}}'`.
          Keys in snake_case as defined in the schemas below.
        - **MANDATORY:** Never end a turn with tool calls only. After EACH
          stack of tool calls produce a short text response, brief confirmation
          plus invitation for the next change.

        # Component Reference

        Components you can add via `activateComponent` + `fillComponent`, or
        edit via `updateComponent`. The five core types (hero, about,
        menu/service, opening_hours, contact_buttons) are typically present
        already; offer optional ones (gallery, location, testimonial, faq,
        cta, pay_now_trigger, text_block, highlight_card, image_with_text,
        image_divider) when the vendor wants more substance.

        {$componentReference}

        # Output Style

        - Respond **briefly** (1–3 sentences per turn).
        - Confirm what changed, ask what's next.
        - No markdown headlines in chat. Sparse emojis if they match voice.
        - No long lists unless explicitly offering options.
        PROMPT;
    }

    /**
     * Lists currently active components and their contents so the agent
     * knows what already exists and whether to use fillComponent (new) or
     * updateComponent (existing).
     */
    private function buildCurrentPageState(): string
    {
        try {
            $loader = app(ContentLoader::class);
            $page = $loader->loadPage($this->vendor->slug, 'home');
        } catch (\Throwable) {
            return '(No feed initialized, this should not happen for a live vendor; investigate.)';
        }

        $components = $page['components'] ?? [];

        if ($components === []) {
            return '(Feed is initialized but no components active. The vendor probably wants to add content.)';
        }

        $lines = ['Active components on the `home` page:'];

        foreach ($components as $component) {
            $type = $component['type'] ?? 'unknown';
            try {
                $loaded = $loader->loadComponent($this->vendor->slug, 'home', $type, $component['file'] ?? '');
                $summary = $this->summarizeFields($loaded['fields'] ?? []);
                $lines[] = "- `{$type}`: {$summary}";
            } catch (\Throwable) {
                $lines[] = "- `{$type}`: (content unreadable)";
            }
        }

        $lines[] = '';
        $lines[] = 'To change one of these → `updateComponent` (NOT fillComponent, that overwrites).';
        $lines[] = 'For new content: check if an existing type already has the matching array (e.g. menu.items), then updateComponent with the extended array.';

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function summarizeFields(array $fields): string
    {
        $parts = [];

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $parts[] = "{$key}=".(mb_strlen($value) > 40 ? mb_substr($value, 0, 37).'…' : $value);
            } elseif (is_array($value)) {
                $parts[] = "{$key}=[".count($value).' items]';
            } else {
                $parts[] = $key;
            }
        }

        return $parts === [] ? '(empty)' : implode(', ', $parts);
    }

    private function buildVendorContext(): string
    {
        return sprintf(
            "- Slug: %s\n- Name: %s\n- Locale: %s\n- Status: %s",
            $this->vendor->slug,
            $this->vendor->name ?: '(empty)',
            $this->vendor->locale,
            $this->vendor->status,
        );
    }

    private function buildComponentReference(): string
    {
        return buildAgentComponentReference();
    }
}
