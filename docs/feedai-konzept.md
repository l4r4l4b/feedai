# FeedAI — Konzept

## Vision

Eine AI-getriebene Mini-Feed-Plattform für Mikro-Businesses in Emerging Markets.
Vendors (Street Food, Taxi mit Touren, Tour-Guides, kleine Service-Anbieter)
bauen per AI-Chat in ihrer Sprache einen modernen Vendor-Feed — an Social Media
angelehnt, aber mit Funktion. Inklusive automatischer Übersetzung in
Tourist-Sprachen, eigener Payment-Seite und einer Contact-Bridge die Nachrichten
zwischen Tourist und Vendor automatisch übersetzt.

### Gelöste Probleme
- **Digitale Sichtbarkeit** für die Kleinsten: keine Webseite, kein Marketing, kein technisches Wissen nötig
- **Sprachbarriere**: Vendor arbeitet in Thai, Tourist liest in seiner Sprache, beide können kommunizieren
- **Payment-Adoption**: Karte, PromptPay, Stablecoin in einer Page, ohne PoS-Hardware und ohne Inventory-Setup

---

## Akteure

- **Public/Tourist**: sieht Vendor-Feeds, zahlt, kontaktiert. Kein Login.
- **Vendor**: legt Feed-Seiten an, editiert, beantwortet Kontakt-Anfragen. Login.
- **Admin (ich)**: Übersicht aller Vendors, Moderation, Payment-Logs.

---

## Kern-Konzept Feed

Jeder Vendor hat einen **Feed** als Hauptseite — mobile-first, scrollbar,
bild-zentriert, an Social Media angelehnt. Zusätzlich kann er beliebige
**Unterseiten** anlegen (z.B. ein Taxifahrer hat eine eigene Seite pro Tour).

Jede Seite basiert auf einem zentralen Standard-Template das alle verfügbaren
Komponenten enthält. Welche Komponenten aktiv sind, entscheidet der AI-Agent
basierend auf den Vendor-Infos. Vendor kann jederzeit Komponenten aktivieren,
befüllen, ändern oder deaktivieren — per Chat oder direkt im Edit-Modus.

---

## Bereiche

### 1. Onboarding (Vendor)

Kurzer AI-Chat-Dialog mit Grundinfos:
- Name / Business-Name
- Was machst du?
- Wo bist du?
- Bilder (optional, aber gewünscht)

Danach entscheidet AI auf Basis der Grundinfos welche Komponenten aus dem
Standard-Template Sinn machen, aktiviert sie und befüllt sie direkt mit dem
was bereits aus dem Dialog bekannt ist. Anschließend fragt AI gezielt nach
was noch fehlt (Telefonnummer, Preise, weitere Bilder).

Wenn alle aktiven Komponenten gefüllt sind: Onboarding abgeschlossen, Feed
geht live.

### 2. Feed Builder (Vendor)

Vendor kann jederzeit:
- Unterseiten anlegen (gleiche Mechanik wie Onboarding: Grundinfos → AI füllt → fragt nach)
- Komponenten aktivieren oder deaktivieren
- Komponenten direkt im Form-Drawer editieren
- Komponenten per AI-Chat ändern lassen
- Komponenten neu sortieren

Zwei parallele Edit-Wege:
- **Direkt-Edit**: Tap auf Komponente → Side-Drawer mit Form-Feldern (automatisch aus Komponenten-Schema generiert)
- **AI-Chat**: Vendor schreibt/spricht Änderungen, gleicher Agent wie beim Onboarding bearbeitet via Tools

Beide Wege nutzen denselben Tool-Layer und dasselbe YAML/MD-Storage.
Änderungen sind im Preview sofort sichtbar.

### 3. Komponenten-Bibliothek

**Spezifische Komponenten** (klare semantische Bedeutung)
- Hero (Bild, Name, One-Liner)
- About (Beschreibung)
- Service (Dienstleistung mit Preis)
- Menu (Items mit Bild + Preis)
- FAQ
- Opening Hours
- Location (Map + Adresse)
- Contact Buttons (WhatsApp, Line, Facebook, Call)
- Gallery
- Testimonial
- CTA
- Contact Form (mit AI-Translation-Bridge)
- Pay Now Trigger (verlinkt auf Payment-Seite)

**Generische Bausteine** (Fallback und visuelle Variation)
- Image Divider (Bild über volle Breite, optional Headline/Subheadline)
- Image with Text (Bild oben, Textblock unten, optional Headline)
- Text Block (nur Text, optional Headline)
- Highlight Card (Icon/Emoji + Headline + Text, für kurze Hinweise)

Spezifische Komponenten werden vom AI-Agent bevorzugt wenn Vendor-Input
semantisch passt. Generische Bausteine kommen zum Einsatz für Inhalte die
nicht in ein spezifisches Schema passen, oder zur visuellen Auflockerung
zwischen großen Sektionen.

### 4. Template-System

Ein zentrales Feed-Template enthält alle Komponenten. Bei Vendor-Anlage wird
diese Struktur als Vendor-Feed-Struktur kopiert, alle Komponenten initial
inaktiv. AI aktiviert nur was passt.

Page-Templates für Unterseiten basieren auf der gleichen Komponenten-Bibliothek.

Optik bleibt im Hackathon-Scope minimal: ein cleanes Template, mobile-first,
neutrale warme Töne (siehe feedai-design.md). Visuelle Individualisierung
(Farben tauschen, Logo, weitere Templates) erst wenn Zeit übrig ist.

### 5. Auto-Translation

Drei Sprachen ab Start: **Thai, Englisch, Deutsch**.

Bei jedem Content-Update läuft ein async TranslationJob via Queue, übersetzt
die betroffene Komponente und speichert die Übersetzung in `translations/{lang}/`.

Tourist sieht Feed automatisch in Browser-Sprache, kann manuell toggeln.
Vendor arbeitet in Thai, sieht optional Preview in EN/DE.

### 6. Public Feed

- `feedai.xyz/{vendor-slug}` rendert den Vendor-Feed
- `feedai.xyz/{vendor-slug}/{page-slug}` rendert Unterseiten
- `feedai.xyz/{vendor-slug}/pay` rendert die Payment-Seite

Mobile-first, schnell, Language-Toggle TH/EN/DE.

### 7. Payment-Seite

Eigenständige Page pro Vendor, je nach Konfiguration unterschiedliche Funktionen:

- **Fixed Amount Buttons** (konkrete Tour-Preise, Menu-Items)
- **Custom Amount Input** (Tip, freier Betrag)
- **Multi-Item Select** (mehrere Items zusammen auswählen)

Zahlungsmethoden je nach Vendor-Setup:
- Stripe (Sandbox im Hackathon)
- PromptPay (QR Code Generator, lokale Library)
- Stablecoin Wallet (Adresse + Chain)

Payment-Seite als eigene URL teilbar, wird von Feed und Unterseiten verlinkt.
Bewusst kein Shop, kein Inventory — nur die Brücke um ad-hoc eine Zahlung
vor Ort entgegenzunehmen.

### 8. Contact Bridge

**Killer Feature.** In-App Chat zwischen Tourist und Vendor mit unsichtbarer Übersetzung dazwischen.

**Tourist-Seite (public, kein Login):**
1. Tourist öffnet Contact-Form auf dem Vendor-Feed, sendet erste Nachricht in seiner Sprache
2. Nach dem Absenden landet er in einer Chat-View (Conversation per Signed-URL-Token zugänglich, kein Account nötig)
3. Tourist sieht die Konversation in seiner Sprache, kann jederzeit weitere Nachrichten schicken
4. Bei neuer Vendor-Antwort: Email-Notification an Tourist mit Link zurück zur Chat-View

**Vendor-Seite (eingeloggt, im Dashboard):**
1. Neue Tourist-Nachrichten landen im **Posteingang** des Vendor-Dashboards
2. Vendor sieht alle Conversations als Liste, ungelesene markiert
3. Beim Öffnen einer Conversation: Chat-View in Thai, alle Tourist-Nachrichten sind bereits übersetzt
4. Vendor antwortet in Thai direkt im Dashboard
5. Bei neuer Tourist-Nachricht: Email-Notification an Vendor mit Link ins Dashboard

**Übersetzungs-Layer (unsichtbar):**
- Jede Nachricht wird beim Eintreffen einmal übersetzt und in beiden Sprachen gespeichert
- Empfänger sieht immer seine Sprache, der Original-Text bleibt im Backend
- TranslationJob via Queue, gleicher Mechanismus wie Content-Translation

**Postmark** wird nur als Mail-Driver für Notifications verwendet (neue Nachricht eingetroffen + Link). Die eigentliche Konversation findet im UI statt, nicht in Email-Threads.

Beide Seiten kommunizieren in ihrer eigenen Sprache. Tool ist unsichtbarer
Übersetzungs-Layer dazwischen.

### 9. Vendor Dashboard

- Live-Preview des eigenen Feeds (mobile-first)
- Komponenten direkt im Preview anklickbar zum Editieren
- AI-Chat sticky am unteren Rand (mobile) bzw. rechts (desktop)
- Posteingang für Kontakt-Nachrichten (in Thai übersetzt)
- Payment-Settings
- Earnings-Log (simple Liste)

### 10. Admin Bereich

- Vendor-Liste mit Status (draft/live/disabled)
- Quick-Preview pro Vendor
- Payment-Log
- Moderation

---

## Tech-Stack

| Bereich | Lösung |
|---|---|
| Framework | Laravel 13.7 + Livewire 4.1 (offizielles Livewire Starter Kit) |
| PHP | 8.3+ |
| Auth | Laravel Fortify (im Starter Kit), Single-Guard mit Role-Column (vendor/admin) |
| Dev-Environment | Laravel Sail (Docker) |
| Frontend | Tailwind + Alpine.js |
| UI-Library | Flux UI 2.x Free (im Starter Kit, MIT) — nur für Auth/Settings/Dashboard-Shell. Public Feed + AI-Chat + Feed-Builder custom mit Tailwind nach feedai-design.md |
| Livewire-Variante | Klassisch (Class in `app/Livewire/` + Blade in `resources/views/livewire/`). Kein Volt. |
| Routing | Livewire 4 File-based Pages (`resources/views/pages/`) via `Route::livewire()` |
| AI-Layer | Laravel AI SDK (in Laravel 13 production-stable, Anthropic-Provider) für Chat, Vision, Translation |
| Media | Spatie Media Library (Uploads, Conversions) |
| Mail | Postmark (nur Notifications: neue Chat-Nachricht, neue Conversation) |
| Payments | Stripe SDK (Sandbox im Hackathon) |
| Queue | Database Queue (Translation Jobs, Notification Jobs, Onboarding Jobs) |
| Dev / AI-Tooling | **Laravel Boost** (MCP-Server für Claude Code, liefert Laravel-13-Docs + Skills während des Codings) |
| Testing | Pest (im Starter Kit), Tinker |

---

## Content-Storage

YAML + Markdown statt komplexes DB-Schema für Page-Content:

```
storage/vendors/{slug}/
├─ vendor.yaml                              ← Stammdaten, Template-Referenz, Status
├─ pages/
│   ├─ home.yaml                            ← Aktive Komponenten + Reihenfolge
│   └─ {subpage-slug}.yaml
├─ content/
│   ├─ home/
│   │   ├─ 01-hero.md                       ← Vendor-Content pro Komponente
│   │   ├─ 02-about.md
│   │   └─ ...
│   └─ {subpage-slug}/...
├─ translations/
│   ├─ en/home/01-hero.md                   ← Übersetzungen
│   ├─ de/home/01-hero.md
│   └─ ...
└─ media/                                   ← Vendor-Bilder (via Spatie Media Library)
```

Code-seitig (fix, für alle Vendors geteilt):

```
component-schemas/
├─ hero.yaml                                ← Welche Felder, welche Typen, required/optional
├─ menu.yaml
└─ ...

feed-templates/
└─ default.yaml                             ← Standard-Feed mit allen Komponenten

resources/views/components/feed/
├─ hero.blade.php                           ← Wie eine Komponente aussieht
├─ menu.blade.php
└─ ...
```

Datenbank nur für: users, vendors, conversations, messages, payments,
jobs (Standard Laravel).

### Drei Datei-Ebenen

| Datei-Typ | Zweck | Wer schreibt |
|---|---|---|
| Blade Components (`.blade.php`) | Wie eine Komponente aussieht | Du, einmalig |
| Component Schemas (`component-schemas/*.yaml`) | Welche Felder eine Komponente braucht | Du, einmalig |
| Feed Template (`feed-templates/default.yaml`) | Welche Komponenten verfügbar sind | Du, einmalig |
| Page Definitions (`pages/*.yaml`) | Aktive Komponenten + Reihenfolge pro Seite | AI via Tools |
| Content Files (`content/**/*.md`) | Echter Vendor-Content | AI via Tools, Vendor via Form |
| Translations (`translations/**/*.md`) | Übersetzte Versionen | TranslationJob automatisch |

---

## AI-Layer

### Funktionsweise

AI-Agent (Claude Sonnet via Prism) führt Konversation mit Vendor und
manipuliert Feed-Content über strikt definierte PHP-Tools. Keine direkte
File-Manipulation durch AI, sondern Tool-Calls die intern validieren und
YAML/MD-Files schreiben.

### Tool-Layer

```
- initializeVendorFeed(grundinfos)          → legt Vendor-Struktur aus Standard-Template an
- activateComponent(pageSlug, type)         → Komponente aktiv setzen
- fillComponent(pageSlug, type, fields)     → Komponente mit Content befüllen
- deactivateComponent(pageSlug, type)       → Komponente entfernen
- uploadImage(base64, intendedUse)          → Bild speichern via Spatie
- createSubpage(slug)                       → neue Unterseite anlegen
- reorderComponents(pageSlug, order)        → Reihenfolge ändern
- updateComponent(pageSlug, type, fields)   → Edit-Modus (wie fillComponent, später)
- finalizeOnboarding()                      → Status auf live
```

Jedes Tool: validiert Input gegen Component-Schema, schreibt atomar,
triggert TranslationJob.

### Onboarding-Strategie

Template-driven, nicht open-ended:
1. AI sammelt Grundinfos im Dialog
2. AI entscheidet welche Komponenten aktiviert werden
3. AI befüllt aktivierte Komponenten direkt mit vorhandenen Infos
4. AI fragt gezielt nach was fehlt
5. Wenn alles voll: finalize

Gleiche Mechanik für Unterseiten und für Edits. AI ist Content-Filler auf
vorgegebener Struktur, nicht Strukturplaner — das erhöht Determinismus und
reduziert Halluzination.

### Multimodal Input

- Voice: Whisper-Transkription, dann als Text behandelt
- Bilder: direkt als Base64 in Claude-Call (Vision), AI sieht das Bild und kann es richtig einordnen

### Translation Pipeline

Async via Queue. Nach jedem Content-Write wird TranslationJob dispatched.
Job übersetzt die geänderte Komponente in EN/DE, schreibt nach
`translations/{lang}/`. Deduplizierung per Vendor+Page+Component um
Race Conditions zu vermeiden.

### System-Prompt

Enthält:
- Rolle und Sprach-Verhalten (Thai default, Englisch wenn Vendor wechselt)
- Komponenten-Liste mit semantischen Hinweisen (wann welche Komponente)
- Generische Bausteine als Fallback erklärt
- Tool-Beschreibungen
- Onboarding-Flow als Anleitung
- Regeln (eine Frage pro Turn, freundlich, bestätigen bei großen Änderungen)

---

## Sprachen ab Start

- **Thai**: Vendor-Eingabe-Sprache, Default
- **Englisch**: Tourist-Sprache, international
- **Deutsch**: Tourist-Sprache, primärer Demo-Markt

Weitere Sprachen später per Konfiguration ergänzbar.

---

## Out of Scope

- Inventory / Order Management
- Booking-System
- PoS-Hardware-Integration
- Tax Reports
- Multi-User pro Vendor
- Analytics für Vendor
- Native Mobile Apps (PWA-fähig okay)
- Mehrere Feed-Templates (ein Template reicht im Hackathon)
- Visuelle Individualisierung (Farben, Logo) — nur wenn Zeit
- Dark Mode

---

## Konferenz-Kontext

SEABW Vibe Coding Hackathon, 24h, Solo, AWS-sponsored (Kiro Code optional).
Bewertung: Originality 30% / Problem Solving 30% / Completeness 25% / Audience Vote 15%.

---

## Bauplan — grobe Reihenfolge

### Phase 1: Foundation (ca. 2h)
- Laravel 13.7 + Sail Setup via `laravel new feedai` mit Livewire Starter Kit
- Single-Guard mit Role-Column (`users.role` = vendor|admin), Middleware `EnsureVendor` / `EnsureAdmin`
- Postmark, Stripe SDK, Spatie Media Library installieren
- **Laravel Boost** installieren + MCP-Server für Claude Code registrieren (`composer require laravel/boost --dev` → `php artisan boost:install` → `claude mcp add ...`)
- Laravel AI SDK konfigurieren (Anthropic-Provider)
- Tailwind-Config aus feedai-design.md übernehmen (Flux Default-Theme überschreiben)
- Datenbank-Migrations: users (+role), vendors, conversations, messages, payments
- Datenmodell: Eloquent Models + Relationen
- Storage-Disks konfigurieren

### Phase 2: Komponenten-System (ca. 3h)
- Component-Schemas anlegen (YAML pro Komponente)
- Blade-Komponenten anlegen (hero, about, service, menu, contact-form, pay-now-trigger, image-divider, image-with-text, text-block, highlight-card, contact-buttons)
- Feed-Template YAML anlegen (default mit allen Komponenten)
- File-System-Helper: YAML/MD lesen und schreiben, Frontmatter parsen

### Phase 3: AI Layer Foundation (ca. 4h)
- Laravel AI SDK konfiguriert (Anthropic Provider, Claude Sonnet)
- Tool-Klassen schreiben: alle Tools mit Validation
- System-Prompt schreiben (langsam, mit Tests)
- ChatService: Multi-Turn-Conversation mit Tool-Use
- onboarding_sessions Tabelle + Logic

### Phase 4: Onboarding Flow (ca. 3h)
- Vendor-Registration (Standard via Starter Kit)
- initializeVendorFeed Tool: legt Vendor-Struktur aus Template an
- Livewire-Component: Onboarding-Chat-UI (mobile-first)
- Bild-Upload via Spatie integriert
- Test: vollständigen Onboarding-Flow mit echtem Vendor durchspielen

### Phase 5: Public Feed Rendering (ca. 2h)
- Public Route + Controller
- Feed-Renderer: pages/home.yaml lesen, Komponenten-Files laden, an Blade übergeben
- Language-Detection (Browser) + Toggle
- Mobile-first Tailwind Styling

### Phase 6: Translation Pipeline (ca. 2h)
- TranslationJob: liest Komponenten-File, übersetzt via Prism in EN/DE
- Job dispatcht bei jedem fillComponent/updateComponent
- Translations werden gerendert wenn Locale != Thai

### Phase 7: Contact Bridge (ca. 3h)
- Contact-Form-Komponente im Public Feed
- Conversation- und Message-Models (Messages speichern Original + Übersetzung)
- Public Chat-View per Signed-URL-Token (Tourist ohne Account)
- TranslateMessageJob: übersetzt eingehende Nachricht in jeweils andere Sprache
- Vendor-Dashboard Posteingang: Conversation-Liste + Chat-View in Thai
- Email-Notifications via Postmark bei neuer Nachricht (Tourist + Vendor), nur als Trigger zurück ins UI

### Phase 8: Vendor Dashboard (ca. 2.5h)
- Dashboard-Layout: Preview + AI-Chat sticky
- Komponenten im Preview anklickbar machen
- Generischer Form-Drawer aus Component-Schema rendern
- AI-Chat im Dashboard (gleicher Agent wie Onboarding)
- Posteingang für Conversations

### Phase 9: Payment-Seite (ca. 2.5h)
- Payment-Settings UI für Vendor (Stripe Connect oder Test-Modus)
- Public Payment-Page Renderer mit den drei Modi
- Stripe Checkout Integration (Sandbox)
- PromptPay QR Generator (lokale Library)
- Payment-Log

### Phase 10: Admin Bereich (ca. 1h)
- Admin Login
- Vendor-Liste mit Status + Quick-Preview
- Payment-Log

### Phase 11: Polish + Demo (ca. 2h)
- 2-3 echte Vendor-Profile vorbereiten (vorher vor Ort gesammelte Daten)
- README für Repo
- Pitch-Slides
- Demo-Flow durchlaufen, Fehler fixen

**Gesamt: ca. 27h.** Bewusst überdimensioniert geplant — Pufferzeit für
unerwartete Probleme. Wenn alles glatt läuft: Bonus-Features (Voice Input,
PromptPay vollständig, Wallet-Payment, weitere Templates).

### Wenn die Zeit knapp wird, in dieser Reihenfolge wegfallen lassen:
1. Wallet-Payment (Crypto)
2. PromptPay (nur Stripe demonstrieren)
3. Admin-Bereich (nur DB-Zugriff)
4. Earnings-Log
5. Komponenten-Reorder im Dashboard
6. Voice-Input (nur Text)

### Was unbedingt funktionieren muss für die Demo:
- Vendor-Onboarding per Chat
- Feed öffentlich erreichbar mit aktiven Komponenten
- Auto-Translation in mindestens Englisch
- Contact Bridge funktional (Tourist → Vendor → Tourist Loop)
- Eine funktionierende Payment-Methode (Stripe Sandbox)

---

## Referenz-Dokumente

- **feedai-design.md** — Design-System: Farben, Typografie, Komponenten-Specs, Tailwind-Config
- **feedai-mockup.html** — visueller Mockup von Public Feed und Vendor Dashboard
