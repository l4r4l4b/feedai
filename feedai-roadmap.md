# FeedAI — Roadmap

Umsetzungsreihenfolge für den 24h-Hackathon, optimiert auf schnelle visuelle
Feedback-Loops. Outside-in statt inside-out: so früh wie möglich etwas im
Browser sehen, dann Schicht für Schicht dahinter bauen.

Basiert auf `feedai-konzept.md`. Phasen-Inhalte unverändert, Reihenfolge und
Granularität angepasst.

---

## Arbeitsregeln für Agents

**WICHTIG — vor jeder Phase und jedem nicht-trivialen Schritt:**

1. **Laravel Boost MCP ist Pflicht-Lookup vor Implementierung.**
   Boost ist als MCP-Server in Claude Code registriert und liefert
   versionsgenaue Laravel-13- und Livewire-4-Docs sowie Best-Practice-Skills.
   Bevor ein Feature implementiert wird, muss der Agent:
   - die zugehörigen Boost-Skills/Docs konsultieren (Routing, Livewire
     Components, Volt-Free Pattern, Fortify, Queues, Jobs, Tools, Validation,
     etc.)
   - bei Unsicherheit die exakte Laravel-13-API-Signatur über Boost
     verifizieren statt aus Training-Memory zu rekonstruieren
   - Code-Beispiele aus Boost als Referenz nehmen, nicht aus älteren
     Laravel-Versionen
   Faustregel: Wenn der Agent eine Laravel- oder Livewire-API anfasst und
   nicht sicher ist ob die in v13 / Livewire 4 identisch ist → Boost fragen.

2. **Boost MCP für Testing nutzen.**
   Boost stellt Tinker, Artisan-Inspect, Route-Lists, Log-Tail und Test-
   Runner als Tools bereit. Agents müssen diese Tools für Verifikation
   verwenden statt blinde Bash-Calls.

3. **Definition of Done ist bindend.**
   Eine Phase ist erst abgeschlossen wenn die unter "Verifikation"
   beschriebenen Schritte erfolgreich durchgeführt UND dokumentiert wurden
   (kurzer Output-Snippet in die Agent-Response).

4. **Kein Phasen-Skip.** Wenn Verifikation fehlschlägt, Root Cause fixen —
   nicht weitermachen und später aufräumen.

5. **Hardcoded Test-Vendor (`demo`) als Anker.** Ab Phase 1 existiert ein
   fester Demo-Vendor mit handgeschriebenen YAML/MD-Files. Alle Phasen
   verifizieren primär gegen `feedai.test/demo`.

---

## Leitprinzipien

- **Sichtbar vor vollständig.** Eine Komponente die im Browser rendert ist
  mehr wert als zehn Schemas im Repo.
- **Stubs vor echter Integration.** Translation, AI-Provider, Stripe — alles
  startet als Stub und wird später real verkabelt. Entkoppelt Risiko.
- **AI-Layer braucht ein Ziel.** Tools werden erst geschrieben wenn der
  Renderer bereits konsumiert, in welche Files sie schreiben sollen.

---

## Phase 0 — Foundation (läuft im Hintergrund)

**Scope:**
- Fresh Laravel 13.7 + Livewire Starter Kit, Sail, Tailwind, Flux Free, Auth
- Single-Guard mit Role-Column (`users.role` = vendor|admin), Middleware
  `EnsureVendor` / `EnsureAdmin`
- Packages: Postmark, Stripe SDK, Spatie Media Library, Laravel AI SDK
- Laravel Boost installieren + MCP registrieren
- Tailwind-Config aus `feedai-design.md` übernehmen
- Migrations: users (+role), vendors, conversations, messages, payments
- Storage-Disks konfigurieren (`storage/vendors/`)

**Boost-Lookup vorab:** Laravel 13 Installer-Flags, Livewire 4 Starter Kit
Konventionen, Fortify Role-Setup, Spatie Media Library Disks.

**Definition of Done:**
- `php artisan serve` startet ohne Errors
- Login-Flow funktioniert (Register → Login → Dashboard-Redirect)
- `users.role` Column existiert, Middleware blockt korrekt
- `composer show` listet alle geforderten Packages
- `claude mcp list` zeigt `laravel-boost` als registriert

**Verifikation:**
```bash
php artisan about                    # zeigt Laravel 13.7, alle Drivers
php artisan route:list               # Auth-Routes vorhanden
php artisan migrate:status           # alle Migrations green
claude mcp list | grep boost         # MCP registriert
```
Plus: Browser auf `/login`, neuen User registrieren, Redirect ins Dashboard.

---

## Phase 1 — Erste Komponente end-to-end (Hero)

**Scope:**
- Component-Schema `component-schemas/hero.yaml` (Felder, Typen,
  required/optional)
- Blade-Komponente `resources/views/components/feed/hero.blade.php`
- Test-Vendor `storage/vendors/demo/` mit handgeschriebener `vendor.yaml`,
  `pages/home.yaml` (nur Hero aktiv), `content/home/01-hero.md`
- File-System-Helper: YAML lesen, Markdown mit Frontmatter parsen
- Quick-Route `/preview/hero` die den Hero des Demo-Vendors rendert

**Boost-Lookup vorab:** Laravel 13 View Components vs Blade Components,
File Storage API, Symfony YAML Package.

**Definition of Done:**
- Hero-Komponente rendert mit Demo-Daten im Browser
- File-Helper lädt YAML und MD-Frontmatter zuverlässig
- Schema-Validation wirft sauber bei fehlenden required-Feldern

**Verifikation:**
```bash
php artisan tinker
>>> app(\App\Services\ContentLoader::class)->loadComponent('demo', 'home', 'hero')
# erwartet: gefülltes Array aus YAML + MD
```
Browser: `feedai.test/preview/hero` zeigt Hero mit Bild + Headline.

---

## Phase 2 — Public Feed Renderer

**Scope:**
- Public Route + Controller: `feedai.test/{vendor-slug}`
- Feed-Renderer: liest `pages/home.yaml`, lädt aktive Komponenten-Files,
  übergibt strukturiert an Blade
- Sub-Page-Routing: `feedai.test/{vendor-slug}/{page-slug}`
- Mobile-first Tailwind-Shell (Header, Container, neutrale warme Töne aus
  `feedai-design.md`)
- 404-Handling für unbekannte Vendors/Pages

**Boost-Lookup vorab:** Livewire 4 File-based Pages (`Route::livewire()`),
Laravel 13 Routing für dynamische Slugs, Tailwind v4 JIT.

**Definition of Done:**
- `feedai.test/demo` zeigt Demo-Vendor mit Hero (und ggf. weiteren
  manuell aktivierten Komponenten)
- Unbekannter Slug → 404
- Mobile Viewport (375px) rendert sauber ohne Overflow

**Verifikation:**
```bash
php artisan route:list | grep vendor   # öffentliche Vendor-Routes
curl -I feedai.test/demo               # 200 OK
curl -I feedai.test/unknown            # 404
```
Browser: `feedai.test/demo` mobile + desktop, Layout passt zu Design-Doc.

---

## Phase 3 — Komponenten-Bibliothek vervollständigen

**Scope (pro Komponente: Schema + Blade + Eintrag in Demo-YAML):**

Reihenfolge nach Demo-Impact:
1. About, Service, Menu, Contact Buttons, Opening Hours
2. Gallery, Location, FAQ, Testimonial, CTA
3. Image Divider, Image with Text, Text Block, Highlight Card
4. Contact Form (Markup only, Logic in Phase 7)
5. Pay Now Trigger (Markup only, Logic in Phase 8)

Parallel:
- Feed-Template `feed-templates/default.yaml` mit allen Komponenten

**Boost-Lookup vorab:** Blade Component Slots/Attributes für komplexe
Komponenten (Menu, Gallery), Spatie Media Library Conversions für Bilder.

**Definition of Done:**
- Alle Komponenten haben Schema + Blade
- Demo-Vendor zeigt jede Komponente mindestens einmal mit realistischem
  Content
- `feed-templates/default.yaml` listet alle Komponenten verfügbar

**Verifikation:**
```bash
ls component-schemas/                  # alle YAMLs vorhanden
ls resources/views/components/feed/    # alle Blades vorhanden
```
Browser: `feedai.test/demo` scrollt durch komplette Komponenten-Galerie,
jede Komponente rendert ohne Layout-Bruch.

---

## Phase 4 — AI Layer Foundation

**Scope:**
- Laravel AI SDK konfiguriert (Anthropic Provider, Claude Sonnet)
- Tool-Klassen mit Validation gegen Component-Schemas:
  `initializeVendorFeed`, `activateComponent`, `fillComponent`,
  `deactivateComponent`, `uploadImage`, `createSubpage`,
  `reorderComponents`, `updateComponent`, `finalizeOnboarding`
- ChatService: Multi-Turn-Conversation mit Tool-Use
- System-Prompt schreiben (siehe Konzept Abschnitt AI-Layer)
- `onboarding_sessions` Tabelle + Logic
- TranslationJob als **Stub** (gibt Original-Text zurück) — wird in Phase 6 echt

**Boost-Lookup vorab:** Laravel AI SDK Provider-Config, Tool-Definition,
Anthropic Vision API Format, Laravel 13 Queues + Job Batching.

**Definition of Done:**
- Tinker-Session: ChatService nimmt User-Nachricht entgegen, ruft korrektes
  Tool, Tool schreibt valide YAML/MD-Files
- Schema-Validation lehnt malformed Input ab
- TranslationJob wird dispatched (auch wenn Stub)

**Verifikation:**
```bash
php artisan tinker
>>> $service = app(\App\Services\ChatService::class)
>>> $service->handle($sessionId, 'Ich verkaufe Pad Thai am Khao San Road')
# erwartet: Tool-Call activateComponent + fillComponent für Hero/About
>>> ls storage/vendors/test-session/content/home/
# erwartet: 01-hero.md mit echtem Content
php artisan queue:work --once          # TranslationJob läuft durch
```
Browser: nach Tinker-Aufruf zeigt `feedai.test/test-session` den
AI-generierten Content.

---

## Phase 5 — Onboarding Flow + Dashboard-Shell

**Scope:**
- Vendor-Registration (Starter Kit erweitert um Role)
- Dashboard-Layout: Live-Preview links/oben, AI-Chat sticky unten (mobile)
  bzw. rechts (desktop)
- Livewire-Component: Onboarding-Chat-UI mobile-first
- `initializeVendorFeed` Tool-Call beim Onboarding-Start
- Bild-Upload via Spatie integriert (Base64 → AI Vision)
- Komponenten im Preview anklickbar machen
- Generischer Form-Drawer aus Component-Schema rendern (Direkt-Edit)

**Boost-Lookup vorab:** Livewire 4 File Uploads, Livewire 4 Wire-Polling
vs Wire-Stream, Flux UI Drawer-Component, Livewire-zu-Blade-Component-Datafluss.

**Definition of Done:**
- Neuer Vendor: Register → Onboarding-Chat → finalize → eigener Feed live
- Live-Preview aktualisiert nach jedem AI-Tool-Call
- Direkt-Edit via Drawer schreibt in dieselben Files wie der AI-Agent
- Bild-Upload erscheint im Feed

**Verifikation:**
End-to-End-Klick-Test:
1. Browser: `/register`, neuen Vendor anlegen
2. Onboarding-Chat starten, 3–4 Turns durchspielen inkl. Bild
3. Finalize klicken
4. `feedai.test/{neuer-slug}` öffentlich sichtbar
5. Im Dashboard auf Hero klicken → Drawer öffnet sich → Text ändern →
   speichern → Preview reflektiert Änderung

```bash
php artisan queue:work --once          # alle Jobs grün
ls storage/vendors/{neuer-slug}/       # vollständige Struktur
```

---

## Phase 6 — Translation Pipeline (echt)

**Scope:**
- TranslationJob: liest Komponenten-File, übersetzt via AI SDK in EN/DE,
  schreibt nach `translations/{lang}/`
- Deduplizierung per Vendor+Page+Component (Race Conditions vermeiden)
- Dispatch bei jedem `fillComponent` / `updateComponent`
- Renderer: Locale != Thai → Translations laden, Fallback auf Original
- Language-Toggle im Public Feed (TH/EN/DE)

**Boost-Lookup vorab:** Laravel Queue Unique Jobs, Laravel AI SDK Structured
Output für Translation, Locale-Handling in Livewire 4.

**Definition of Done:**
- Vendor editiert Hero in Thai → einige Sekunden später existieren EN+DE
  Files
- Public Feed reagiert auf `?lang=en` / `?lang=de`
- Unique-Job-Constraint verhindert doppelte Übersetzungen

**Verifikation:**
```bash
php artisan tinker
>>> # Hero editieren
>>> app(\App\Services\ChatService::class)->handle($sid, 'Ändere Headline auf "Bestes Pad Thai"')
php artisan queue:work --once
>>> ls storage/vendors/demo/translations/en/home/
# erwartet: 01-hero.md mit englischer Übersetzung
```
Browser: `feedai.test/demo?lang=en` und `?lang=de` zeigen übersetzten
Content, Toggle wechselt Sprache live.

---

## Phase 7 — Contact Bridge

**Scope:**
- Contact-Form-Komponente im Public Feed funktional machen
- Conversation- und Message-Models (Original + Übersetzung speichern)
- Public Chat-View per Signed-URL-Token (Tourist ohne Account)
- TranslateMessageJob: übersetzt eingehende Nachricht in andere Sprache
- Vendor-Posteingang: Conversation-Liste + Chat-View in Thai
- Email-Notifications via Postmark (neue Nachricht + Link), nur als Trigger

**Boost-Lookup vorab:** Laravel 13 Signed URLs, Livewire 4 Polling für
Inbox, Postmark Driver Config, Notification Channels.

**Definition of Done:**
- Tourist-Loop funktional: Form absenden → Chat-View per Token → weitere
  Nachrichten möglich
- Vendor-Loop funktional: Inbox zeigt neue Conversation → Thai-Ansicht →
  Antwort
- Email-Notifications gehen raus (Mailtrap/Log-Driver im Dev reicht)
- Beide Seiten kommunizieren in ihrer eigenen Sprache

**Verifikation:**
End-to-End-Klick-Test:
1. Browser Inkognito: `feedai.test/demo` → Contact-Form → Nachricht auf EN
2. Redirect auf Chat-View per Signed URL — Nachricht sichtbar
3. Browser regulär (eingeloggt als Vendor): Posteingang zeigt Conversation
4. Conversation öffnen → Nachricht steht auf Thai
5. Auf Thai antworten → Submit
6. Inkognito-Browser refresht (Polling) → Antwort steht auf EN

```bash
php artisan queue:work --once
tail -f storage/logs/laravel.log       # Mail-Logs prüfen
```

---

## Phase 8 — Payment-Seite

**Scope:**
- Payment-Settings UI für Vendor (Stripe Sandbox)
- Pay Now Trigger Komponente funktional (verlinkt auf `/pay`)
- Public Payment-Page Renderer mit drei Modi:
  Fixed Amount Buttons, Custom Amount Input, Multi-Item Select
- Stripe Checkout (Sandbox) End-to-End
- Payment-Log einfache Liste
- PromptPay QR + Wallet-Adresse: nur wenn Zeit (optional)

**Boost-Lookup vorab:** Laravel Cashier vs raw Stripe SDK, Stripe Checkout
Sessions, Webhooks-Handling, Signed Webhook Verification.

**Definition of Done:**
- Vendor konfiguriert Payment-Settings
- Tourist durchläuft Stripe Checkout (Sandbox) erfolgreich
- Webhook updated Payment-Log
- Drei Modi sind alle erreichbar und funktional

**Verifikation:**
End-to-End-Klick-Test mit Stripe-Testkarte 4242 4242 4242 4242:
1. Vendor-Dashboard → Payment-Settings → Stripe verbinden
2. Public Feed → Pay Now → Modus wählen → Stripe Checkout
3. Mit Test-Karte zahlen → Success-Redirect
4. Payment-Log zeigt Eintrag

```bash
stripe listen --forward-to feedai.test/webhooks/stripe   # Webhook live
php artisan tinker
>>> \App\Models\Payment::latest()->first()
# erwartet: status=succeeded, korrekter Betrag
```

---

## Phase 9 — Admin Bereich

**Scope:**
- Admin-Login (Role-Check)
- Vendor-Liste mit Status (draft/live/disabled) + Quick-Preview
- Payment-Log read-only
- Conversation-Übersicht read-only (Moderation)

**Boost-Lookup vorab:** Livewire 4 Tables, Pagination, Flux UI Table-Component.

**Definition of Done:**
- Admin sieht alle Vendors mit Status
- Quick-Preview-Link öffnet Public Feed
- Payment-Log und Conversations sichtbar, nicht editierbar

**Verifikation:**
1. Admin-User in DB (Seeder oder Tinker: `User::factory()->admin()->create()`)
2. Browser: Login als Admin → `/admin` → Liste mit Demo-Vendor + neue Vendors
3. Auf Vendor klicken → Public Feed öffnet sich
4. `/admin/payments` zeigt Stripe-Sandbox-Zahlung aus Phase 8

---

## Phase 10 — Polish + Demo

**Scope:**
- 2–3 echte Vendor-Profile vorbereiten (vor Ort gesammelte Daten)
- Demo-Flow durchspielen, Fehler fixen
- README für Repo
- Pitch-Slides
- Edge Cases: leere States, lange Texte, fehlende Bilder

**Definition of Done:**
- Mindestens zwei vollständige Demo-Vendors live
- Compleate End-to-End-Flow läuft fehlerfrei durch
- README erklärt Setup in unter 5 Minuten
- Pitch-Slides decken Problem, Lösung, Demo, Tech ab

**Verifikation:**
- Trockenlauf des kompletten Demo-Flows mit Timer
- Fresh-Clone-Test: Repo clonen, `make setup` (oder Sail-Up), in unter
  5 Min läuft die App lokal

---

## Was wegfallen darf wenn die Zeit knapp wird

In dieser Reihenfolge:
1. Wallet-Payment (Crypto)
2. PromptPay (nur Stripe demonstrieren)
3. Admin-Bereich (nur DB-Zugriff)
4. Earnings-Log
5. Komponenten-Reorder im Dashboard
6. Voice-Input (nur Text)

## Was unbedingt funktionieren muss für die Demo

- Vendor-Onboarding per Chat
- Feed öffentlich erreichbar mit aktiven Komponenten
- Auto-Translation in mindestens Englisch
- Contact Bridge funktional (Tourist → Vendor → Tourist Loop)
- Eine funktionierende Payment-Methode (Stripe Sandbox)

---

## Referenz

- `feedai-konzept.md` — vollständige Spezifikation
- `feedai-design.md` — Design-System
- `feedai-mockup.html` — visueller Mockup
