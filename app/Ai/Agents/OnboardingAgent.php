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
#[MaxSteps(30)]
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
        $currentPageState = $this->buildCurrentPageState();

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

        # Current Feed State

        {$currentPageState}

        # Critical: One Component Per Type

        Jede Komponenten-Typ existiert **maximal einmal pro Page**. Du hast
        NICHT mehrere `service`-Komponenten oder mehrere `menu`-Komponenten.
        Stattdessen halten viele Komponenten **Arrays von Items**:

        - `menu` → ein `items`-Array mit allen Speisen
        - `service` → DIESE Komponente repräsentiert **EINEN Service**. Für
          MEHRERE Touren/Dienste rufe `service` mehrfach mit fortlaufenden
          Page-Slugs auf — d.h. lege Sub-Pages an (`createSubpage` →
          `tour-temples`, `tour-ayutthaya`) und fülle dort jeweils einen
          `service` UND/ODER nutze für Übersichts-Listen `menu` mit den Touren
          als Items.
        - `gallery` → ein `images`-Array
        - `contact_buttons` → ein `buttons`-Array (alle Kanäle gemeinsam)
        - `opening_hours` → ein `hours`-Array (alle Tage)
        - `faq` → ein `items`-Array (alle Fragen)
        - `testimonial` → ein einzelnes Zitat. Mehrere Stimmen → CTA oder
          Image-with-Text mit zusammengefasstem Zitat.

        **Beispiel falsch:** fillComponent('hero', ...) → fillComponent('hero', ...).
        Beim zweiten Call überschreibst du den ersten! Stattdessen: einmal
        fillComponent('hero', {...}) MIT ALLEN finalen Werten gleichzeitig.

        **Beispiel richtig:** Vendor nennt drei Touren →
        fillComponent('menu', {items: [{name: 'Tempel-Tour', price: '2500 THB'},
        {name: 'Ayutthaya', price: '4800 THB'}, {name: 'Floating Market',
        price: '3500 THB'}]}) — ALLE Items in EINEM Call.

        Bevor du fillComponent für einen Typ rufst der bereits aktiv ist
        ("Current Feed State" oben prüfen): nutze **updateComponent** mit dem
        VOLLSTÄNDIGEN neuen Inhalt (inklusive der vorhandenen Items, falls
        Array-basiert). updateComponent ersetzt — es merged NICHT.

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
        6. **Bilder:** Bilder werden VOR dem Agent-Call persistiert + AI-analysiert.
           Wenn die User-Nachricht einen `[IMAGE_UPLOADED url=… suggested_intent=…]`-
           Block enthält, ist das Bild bereits gespeichert. Du musst KEIN
           uploadImage-Tool aufrufen. Stattdessen:
             1. Nutze die `url` direkt als `image`-Feld in `fillComponent` für
                den `suggested_intent`-Komponenten-Typ (z.B. `hero`, `menu_item`,
                `service`, `gallery`).
             2. Falls die Komponente noch nicht aktiv ist → fillComponent aktiviert
                sie automatisch.
             3. Wenn der Vorschlag nicht passt (Vendor hat etwas anderes gemeint),
                frage kurz nach, BEVOR du fillComponent aufrufst.
             4. Nutze die Vision-Beschreibung in deiner Antwort an den Vendor —
                so weiß er, dass du das Bild "gesehen" hast.
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
        - **PFLICHT-Regel:** Beende **niemals** einen Turn ausschließlich mit
          Tool-Calls. Nach JEDEM Stack von Tool-Calls MUSST du eine kurze
          Text-Antwort an den Vendor produzieren — minimal eine Bestätigung
          ("Habe Hero und Menu angelegt.") plus eine **konkrete Folgefrage**
          ("Wie sind deine Öffnungszeiten?"). Ein leerer Text-Response nach
          Tool-Calls lässt den Vendor im Dunkeln sitzen — das passiert NICHT.
        - Wenn mehrere Komponenten parallel gefüllt werden (z.B. 5 Service-Tours
          aus einer Liste), gruppiere die Tool-Calls und liefere danach EINEN
          zusammenfassenden Text mit der nächsten Frage.

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
     * Listet die aktuell aktiven Komponenten + ihre Inhalte, damit der Agent
     * weiß was schon da ist und ob er fillComponent (neu) oder updateComponent
     * (existierend) verwenden muss.
     */
    private function buildCurrentPageState(): string
    {
        try {
            $loader = app(ContentLoader::class);
            $page = $loader->loadPage($this->vendor->slug, 'home');
        } catch (\Throwable) {
            return '(Kein Feed initialisiert — rufe `initializeVendorFeed` als ersten Tool-Call.)';
        }

        $components = $page['components'] ?? [];

        if ($components === []) {
            return '(Feed ist initialisiert, aber noch keine Komponenten aktiv.)';
        }

        $lines = ['Aktive Komponenten auf der `home`-Page:'];

        foreach ($components as $component) {
            $type = $component['type'] ?? 'unknown';
            try {
                $loaded = $loader->loadComponent($this->vendor->slug, 'home', $type, $component['file'] ?? '');
                $summary = $this->summarizeFields($loaded['fields'] ?? []);
                $lines[] = "- `{$type}`: {$summary}";
            } catch (\Throwable) {
                $lines[] = "- `{$type}`: (Inhalt nicht lesbar)";
            }
        }

        $lines[] = '';
        $lines[] = 'Wenn du einen dieser Typen ändern willst → `updateComponent` (NICHT fillComponent — das überschreibt).';
        $lines[] = 'Für neue Inhalte: prüfe ob ein bestehender Typ schon das passende Array hat (z.B. menu.items), dann updateComponent mit dem erweiterten Array.';

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

        return $parts === [] ? '(leer)' : implode(', ', $parts);
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
