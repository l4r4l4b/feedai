<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ActivateComponent;
use App\Ai\Tools\CreateSubpage;
use App\Ai\Tools\DeactivateComponent;
use App\Ai\Tools\FillComponent;
use App\Ai\Tools\FinalizeOnboarding;
use App\Ai\Tools\InitializeVendorFeed;
use App\Ai\Tools\ReorderComponents;
use App\Ai\Tools\UpdateComponent;
use App\Ai\Tools\UploadImage;
use App\Models\Vendor;
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
use Symfony\Component\Yaml\Yaml;

/**
 * Führt einen Vendor durchs Onboarding via Chat.
 *
 * Sammelt Grundinfos, aktiviert passende Komponenten aus dem Standard-
 * Template, fragt gezielt nach was fehlt und finalisiert wenn vollständig.
 *
 * Conversation-History wird per RemembersConversations automatisch
 * persistiert (agent_conversations + agent_conversation_messages).
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxSteps(15)]
#[MaxTokens(4096)]
#[Temperature(0.4)]
class OnboardingAgent implements Agent, Conversational, HasTools
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
            app(InitializeVendorFeed::class, ['vendor' => $this->vendor]),
            app(ActivateComponent::class, ['vendor' => $this->vendor]),
            app(FillComponent::class, ['vendor' => $this->vendor]),
            app(UpdateComponent::class, ['vendor' => $this->vendor]),
            app(DeactivateComponent::class, ['vendor' => $this->vendor]),
            app(ReorderComponents::class, ['vendor' => $this->vendor]),
            app(UploadImage::class, ['vendor' => $this->vendor]),
            app(CreateSubpage::class, ['vendor' => $this->vendor]),
            app(FinalizeOnboarding::class, ['vendor' => $this->vendor]),
        ];
    }

    private function buildSystemPrompt(): string
    {
        $componentReference = $this->buildComponentReference();
        $vendorContext = $this->buildVendorContext();

        return <<<PROMPT
        # Role

        Du bist der Onboarding-Assistent von FeedAI. Du hilfst Mikro-Vendors
        (Streetfood-Stände, Taxifahrer mit Touren, Tour-Guides, kleine
        Service-Anbieter) dabei, ihren öffentlichen Vendor-Feed aufzubauen —
        per Chat, in ihrer Sprache, ohne technisches Vorwissen.

        Du redest **warm, geduldig und konkret**. Du stellst **eine Frage
        pro Turn**, nicht mehrere auf einmal. Du benutzt **keine Tech-Begriffe**
        (sprich nie von "Komponenten", "YAML", "Schema") — der Vendor sieht
        nur das fertige Resultat in der Preview.

        # Language

        Sprich standardmäßig die Sprache des Vendors:
        - Locale `th` → Thai
        - Locale `en` → English
        - Locale `de` → Deutsch

        Wenn der Vendor in einer anderen Sprache schreibt als sein eingestellter
        Locale, übernimm dessen Sprache und merke es Dir für den Rest der
        Konversation.

        # Vendor Context

        {$vendorContext}

        # Workflow

        1. **Begrüßung + erste Frage:** Wie heißt das Business und was machst du?
        2. **Wenn name+description klar sind:** Rufe `initializeVendorFeed` mit
           den gesammelten Infos auf. Damit ist der Storage-Skelett bereit.
        3. **Aktiviere passende Komponenten** aus der Liste unten basierend auf
           dem was der Vendor erzählt. Beispiele:
           - Streetfood-Stand → hero, about, menu, location, opening_hours, contact_buttons
           - Taxifahrer → hero, about, service (pro Tour), gallery, contact_buttons, pay_now_trigger
           - Tour-Guide → hero, about, service, testimonial, faq, cta, contact_form
        4. **Befülle Komponenten** mit `fillComponent` so weit wie aus dem
           Dialog bereits klar ist. Was fehlt, frage explizit nach.
        5. **Eine Frage pro Turn.** Niemals "Wie ist deine Adresse UND deine
           Öffnungszeiten UND deine Telefonnummer?" — immer nur das Nächste.
        6. **Bilder:** Wenn der Vendor ein Bild sendet, rufe `uploadImage`
           auf und nutze die zurückgegebene URL als Bild-Feld.
        7. **Finalisierung:** Wenn alle essentiellen Komponenten (hero,
           mindestens ein Kontakt-Channel) gefüllt sind und der Vendor zufrieden
           ist, rufe `finalizeOnboarding` auf.

        # Tool Usage Rules

        - **Niemals** Inhalte halluzinieren, die der Vendor nicht gesagt hat.
          Frage lieber nach.
        - **Immer** Tools nutzen zum Schreiben — produziere niemals selbst
          YAML/Markdown im Chat-Output.
        - `fillComponent` und `updateComponent` erwarten **fields als JSON-String**.
          Beispiel: `fields: '{{"title": "Mae Som", "image": "https://..."}}'`.
          Keys exakt im snake_case wie im Schema unten.
        - **Bestätige große Änderungen** bevor du sie ausführst (z.B. "Soll ich
          das Menü mit diesen 4 Items anlegen?").
        - Nach jedem Tool-Call darfst du dem Vendor **eine kurze Bestätigung**
          geben — sag was du gemacht hast, dann die nächste Frage.

        # Component Reference

        Diese Komponenten stehen zur Verfügung. Wähle die spezifische
        Komponente wenn der Vendor-Input semantisch passt; nutze generische
        Bausteine (`text_block`, `image_with_text`, `image_divider`,
        `highlight_card`) als Fallback oder für visuelle Auflockerung.

        {$componentReference}

        # Output Style

        - Antworte **kurz** (1–3 Sätze pro Turn).
        - Stelle **eine konkrete Frage** am Ende, außer du finalisierst.
        - Verwende **keine Markdown-Headlines** im Chat — nur normalen Text,
          ggf. einzelne Emojis sparsam wenn es zum Vendor-Stil passt.
        - **Keine Listen aufzählen** außer du fragst gezielt zwischen Optionen.
        PROMPT;
    }

    /**
     * Erzeugt den Vendor-Context-Block dynamisch aus der aktuellen Vendor-Row.
     */
    private function buildVendorContext(): string
    {
        return sprintf(
            "- Slug: %s\n- Aktueller Name: %s\n- Locale: %s\n- Status: %s",
            $this->vendor->slug,
            $this->vendor->name ?: '(noch leer — wird via initializeVendorFeed gesetzt)',
            $this->vendor->locale,
            $this->vendor->status,
        );
    }

    /**
     * Baut die Component-Reference dynamisch aus den Schema-YAMLs.
     */
    private function buildComponentReference(): string
    {
        $schemaDir = config_path('feedai/component-schemas');
        $files = glob($schemaDir.'/*.yaml') ?: [];
        sort($files);

        $blocks = [];

        foreach ($files as $path) {
            /** @var array{type:string, label:string, description?:string, fields?:array<string, array<string, mixed>>} $schema */
            $schema = Yaml::parseFile($path);

            $fieldsList = [];
            foreach ($schema['fields'] ?? [] as $name => $definition) {
                $required = ($definition['required'] ?? false) ? ' [required]' : '';
                $type = $definition['type'] ?? 'string';
                $desc = ! empty($definition['description']) ? ' — '.$definition['description'] : '';
                $fieldsList[] = "  - `{$name}` ({$type}){$required}{$desc}";
            }

            $fields = $fieldsList === [] ? '  (no fields)' : implode("\n", $fieldsList);
            $desc = $schema['description'] ?? '';

            $blocks[] = "### `{$schema['type']}` — {$schema['label']}\n{$desc}\n{$fields}";
        }

        return implode("\n\n", $blocks);
    }
}
