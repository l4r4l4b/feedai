# FeedAI — Design System v2

Visuelle Sprache, Tokens und Komponenten-Prinzipien für FeedAI.
Monochrom-modern, bild-zentriert, große Radien, bold Typo. UI ist
schwarz/weiß damit Vendor-Fotos die einzige Farbquelle sind. Jeder Vendor
kann optional **eine** Akzentfarbe wählen die nur auf interaktive Elemente wirkt.

Referenz-Look: moderne Travel-Apps (Airbnb-nah), monochrom mit Foto-Fokus,
schwebende Pill-Nav, Pill-CTAs mit rundem Arrow-Button.

---

## Design-Prinzipien

1. **Monochrome & bold.** Schwarz, Weiß, Grau. Farbe kommt ausschließlich aus Vendor-Fotos (und dem grünen Live-Status-Dot).
2. **Big rounded everything.** Cards 22px, Hero 28px, Buttons als volle Pills. Die großen Radien sind der Haupttreiber des modernen Looks.
3. **Photos fill the frame.** Bilder randlos in Cards, Text liegt drauf (Overlay) oder direkt darunter, ohne Schnickschnack.
4. **Pill CTAs with arrow.** Primary-Actions sind volle schwarze Pills. Der runde Arrow-Button (->) ist das Signature-Element.
5. **Floating nav (dashboard only).** Schwebende schwarze Pill-Nav im Vendor-Dashboard. Der Public Feed bleibt nav-frei (reine Scroll-Seite).
6. **Heavy display weights.** Headlines in 800, tight letter-spacing (-0.02em bis -0.03em). Selbstbewusst, freundlich.
7. **AI invisible.** Keine Sparkles, keine Gradients, keine "Magie". AI ist ein Werkzeug das der Vendor nie sehen muss.

---

## Color Tokens

### Core (monochrom)

| Token | Hex | Verwendung |
|---|---|---|
| `canvas` | `#FFFFFF` | App-Hintergrund, reines Weiß |
| `surface` | `#F6F6F7` | Sekundäre Flächen, soft Cards, Input-Felder |
| `ink` | `#0A0A0B` | CTAs, aktive States, Bottom-Nav, near-black |
| `ink-soft` | `#1A1A1D` | Alternative dunkle Fläche |
| `text` | `#18181B` | Primärer Text |
| `muted` | `#71717A` | Sekundärer Text, Labels |
| `soft-muted` | `#A1A1AA` | Placeholder, Captions, disabled |
| `line` | `#ECECEE` | Borders, Trennlinien |
| `line-soft` | `#F2F2F4` | sehr leichte Trennungen |

### Status (einzige feste Farbe)

| Token | Hex | Verwendung |
|---|---|---|
| `live` | `#22C55E` | Live-Status-Dot, "Filled"-Badge. NUR für Status, nie dekorativ |

### Vendor-Akzent (optional, pro Vendor)

| Token | Default | Verwendung |
|---|---|---|
| `accent` | `= ink` (`#0A0A0B`) | Ersetzt Ink auf interaktiven Elementen wenn Vendor eine Farbe wählt |

**Akzent-Logik:**
- Wenn der Vendor **keinen** Akzent wählt: `accent` = `ink` (alles bleibt schwarz/weiß).
- Wenn der Vendor einen Akzent wählt: `accent` ersetzt das Schwarz NUR auf:
  - Primary-Buttons / Pill-CTAs
  - Der runde Arrow-Button
  - Aktive Pills (Sprach-Toggle aktiv, Tab aktiv)
  - Der Pay-CTA-Block
  - Radio-Selection auf der Payment-Seite
- **Nicht** betroffen: Text, Borders, Surfaces, Hero-Overlays, Icons, Bottom-Nav. Die bleiben immer monochrom.
- Akzent sollte dunkel genug für weißen Text drauf sein. Bei Auswahl ggf. automatisch abdunkeln (min. Kontrast-Ratio 4.5:1 gegen Weiß sicherstellen).

Beispiel-Akzente die gut funktionieren: Petrol `#0F5C5C`, Terrakotta `#B5483A`, Deep Purple `#4C3A8C`, Forest `#2D5A3D`.

---

## Typografie

### Schriften

- **Plus Jakarta Sans** — Display und Body (Latin). Gerundete Grotesk mit Charakter.
- **Anuphan** — Thai-Glyphen. Passt von der Rundung gut zu Jakarta.

Geladen via Google Fonts, OFL-Lizenz, kostenfrei.

CSS-Stack: `font-family: 'Plus Jakarta Sans', 'Anuphan', sans-serif;`

### Gewichte

- **400 Regular** — Body, langer Text
- **500 Medium** — Buttons, Labels, hervorgehobener Text
- **600 Semibold** — kleinere Headlines, Captions mit Gewicht
- **700 Bold** — Sub-Headlines
- **800 Extrabold** — Display, Hero-Titel, Section-Titel

### Skala

| Stufe | Größe | Gewicht | Letter-Spacing | Line-Height | Verwendung |
|---|---|---|---|---|---|
| Display | 26-30px (mobile) / 40px+ (desktop) | 800 | -0.03em | 1.1 | Vendor-Name, Page-Title, Amount |
| Section | 19-22px | 800 | -0.02em | 1.2 | Sektionen, Card-Headlines |
| Title | 17px | 800 | -0.01em | 1.2 | Service-Titel, Item-Namen |
| Body | 15-16px | 400 | 0 | 1.6 | Beschreibungen |
| Label | 13-14px | 700 | 0 | 1.4 | Buttons, Tabs, kleine Labels |
| Caption | 12px | 600 | 0.02em | 1.5 | Meta-Info, Subtexte, Trust-Hinweise |

### Regeln

- Headlines aggressiv in 800 mit tight spacing. Das ist der Look.
- Body bleibt 400 mit großzügiger Line-Height (1.6).
- Keine Caps außer in winzigen Labels (Pay-Label, Badges) — dann 600/700 + letter-spacing 0.03em.

### Thai-Spezifika

- Font: Anuphan
- Line-Height etwas höher als Latin (1.55-1.65 statt 1.5)
- Kein negatives Letter-Spacing bei Thai-Headlines (Thai braucht den Raum)
- Bei Headlines Thai eher Gewicht 700 statt 800 (Anuphan wirkt bei 800 sehr dicht)

---

## Radien

Große Radien sind zentral für den Look. Großzügig bleiben.

| Token | Wert | Verwendung |
|---|---|---|
| `r-xl` | 28px | Hero-Card, Amount-Block, große Feature-Cards |
| `r-lg` | 22px | Standard-Cards (Service, Highlight, Contact Form, Divider) |
| `r-md` | 16px | Kleinere Cards, Inputs, Component-Items, Bilder in Cards |
| `r-sm` | 12px | Icon-Container, kleine Elemente |
| `pill` | 999px | Buttons, CTAs, Toggles, Badges, Bottom-Nav, Avatar, Arrow-Button |

Buttons sind **immer** volle Pills (999px), nie nur leicht gerundet.

---

## Schatten

Sehr sparsam. Flat-Design, Tiefe nur wo nötig.

- Cards: in der Regel **keine** Schatten, Abgrenzung über `surface`-Background oder `line`-Border
- Floating Bottom-Nav: `0 10px 30px rgba(0,0,0,0.3)` (die darf schweben)
- Phone-Frame (nur Mockup): `0 30px 60px -20px rgba(0,0,0,0.25)`
- Status-Dot: `0 0 0 4px rgba(34,197,94,0.15)` Glow

---

## Bilder

### Aspect Ratios

| Verwendung | Ratio | Notiz |
|---|---|---|
| Hero | 3:4 | Großes Foto mit Text-Overlay unten |
| Service / Card-Bild | 16:10 | randlos in Card, Favoriten-Herz oben rechts |
| Divider | 16:9 | volle Card-Breite, Text-Overlay |
| Menu / Product | 1:1 | konsistent im Grid |
| Gallery | 1:1 oder 4:5 | konsistent pro Gallery |

### Regeln

- Bilder füllen ihre Container randlos, Radius vom Container.
- Hero und Divider: Gradient-Overlay von unten (`rgba(0,0,0,0.7-0.85)` -> transparent) für Text-Lesbarkeit.
- Keine Filter auf Vendor-Fotos.
- Favoriten-Herz / Badges auf Bildern: weißer Blur-Background (`rgba(255,255,255,0.9)` + `backdrop-filter: blur(8px)`).
- Spatie Media Library für Konvertierung (webp, responsive sizes), lazy-loading default.

---

## Komponenten

### Hero (Public Feed)

- **Format:** 3:4 Foto-Card, `r-xl` (28px)
- **Overlay:** Gradient von unten, Text weiß drauf
- **Inhalt:** Location-Zeile (Pin mit Caption-Style), Vendor-Name (Display 800), Rating-Pill (blur-bg)
- **Favoriten-Herz:** oben rechts, 40px Kreis, weißer blur-bg

### About

- **Layout:** optional Section-Label drüber, dann Body-Text
- **"Read more":** Ink-farbig, bold, unterstrichen, kürzt langen Text
- **Body:** 15px, line-height 1.6

### Service Card

- **Background:** `surface`, `r-lg` (22px), 12px Padding
- **Bild:** 16:10, `r-md`, randlos, Favoriten-Herz oben rechts
- **Inhalt:** Titel (Title 800) -> Meta (Caption muted) -> Bottom-Row
- **Bottom-Row:** Rating + Preis links, runder Arrow-Button (`accent`, 44px) rechts

### Image Divider

- **Format:** 16:9, `r-lg`, Gradient-Overlay
- **Text:** weiß unten-links, Headline (Section 800) + Sub (Caption)

### Highlight Card

- **Background:** `surface`, `r-lg`
- **Icon-Box:** 46px, `ink`-bg, weißes Icon, `r-sm`
- **Inhalt:** Titel (700) + Beschreibung (Caption muted)

### Contact Buttons

- **Layout:** 2-spalten Grid
- **Style:** weiß mit `line`-Border (1.5px), `r-md`, bold Label
- **Icon:** 28px gerundetes Quadrat in Channel-Farbe, weißes Symbol
- **Channel-Farben** (Ausnahme von monochrom, da Markenwiedererkennung): WhatsApp `#25D366`, LINE `#06C755`, Facebook `#1877F2`, Call `ink`

### Contact Form

- **Background:** `surface`, `r-lg`, 22px Padding
- **Titel:** Section 800
- **Sub:** erklärt die Translation-Bridge explizit
- **Inputs:** `canvas`-bg, `line`-Border 1.5px, `r-sm`
- **CTA:** volle Pill, `accent`, weißer Text, bold
- **Note:** Caption soft-muted, "Auto-translated both ways"

### Pay CTA (im Feed)

- **Background:** `accent` (default ink), `r-lg`
- **Inhalt:** kleines Label (caps, 700) + Titel (Section 800) links, runder Arrow-Button rechts (`rgba(255,255,255,0.14)` bg)
- verlinkt auf die separate Payment-Seite

### Generische Bausteine

- **Image with Text:** Bild (16:9/4:3) `r-md` oben, Text drunter, optional Headline
- **Text Block:** optional Headline (Section), Body 15px/1.6
- **Highlight Card:** siehe oben, Icon kann Emoji sein

---

## Payment-Seite

### Header
- Back-Button (40px Kreis, `line`-Border) + Titel (Title 800)

### Amount-Block
- `ink`-bg (oder `accent`), `r-xl`, zentriert, weiß
- Label (Caption) -> Amount (Display 800, 44px) -> Umrechnung (Caption, ca. $ / EUR)

### Payment-Methoden (Cards)
- weiß mit `line`-Border, `r-md`, ausgewählt = `accent`-Border 2px
- Icon-Box 44px `surface`-bg -> Name (700) + Desc (Caption) -> Radio oder Badge
- **Badges:** "Direct" für PromptPay und Crypto (= geht direkt an Vendor)
- **Radio:** nur bei Card (platform-processed), gefüllt mit `accent`

### CTA + Trust
- volle Pill `accent`, "Pay [Amount] with [Method]"
- Trust-Note darunter: "FeedAI never holds your money. PromptPay & crypto go straight to the vendor."

---

## Dashboard-Komponenten

### Header
- Titel "My Feed" (Display 800), Avatar (38px Gradient-Kreis)

### Tabs (Seiten)
- Pills, horizontal scrollbar, aktiv = `accent`-gefüllt weiß-Text, inaktiv = weiß `line`-Border muted
- letzter Tab "+ Page"

### Status-Zeile
- `surface`-bg, `r-md`, Live-Dot (grün, glow) + Status-Text + "Preview"-Link

### Component-Item
- weiß `line`-Border `r-md`, aktiv = `accent`-Border 2px
- Icon-Box 38px (`surface`, aktiv = `ink`-bg weiß) + Name (700)/Meta (Caption) + State-Badge
- **State-Badge:** "Filled" (live-grün tint), "+ X" (muted tint, fehlt noch)

### AI Chat
- `surface`-bg, `r-lg`, 18px Padding
- Header: "AI Assistant" mit grünem Dot + Sprach-Meta
- Bubble AI: `canvas`-bg, radius 16px (bottom-left 5px)
- Bubble User: `accent`-bg weiß, margin-left auto (bottom-right 5px)
- Thai-Bubbles: Anuphan
- Input: Pill, `canvas`-bg `line`-Border, Send-Button rund `accent`

### Floating Bottom-Nav
- `ink`-bg (immer schwarz, NICHT accent — Nav bleibt neutral), Pill, schwebt 20px über unterem Rand
- Items: 52px Kreise, aktiv = weiß-bg ink-Icon, inaktiv = `rgba(255,255,255,0.5)`
- Reihenfolge: Feed / Inbox / Pay / Profil

---

## Logo / Wortmarke

`Feed` in `ink`, `AI` in `soft-muted`, Plus Jakarta Sans 800, letter-spacing -0.02em.

Das tiefergestellte `AI` (heller, Gewicht 600) hält AI als unsichtbares Werkzeug
im Hintergrund statt als Verkaufsargument.

---

## Tailwind Config

```js
// tailwind.config.js — relevante Custom Tokens

theme: {
  extend: {
    colors: {
      canvas: '#FFFFFF',
      surface: '#F6F6F7',
      ink: '#0A0A0B',
      'ink-soft': '#1A1A1D',
      text: '#18181B',
      muted: '#71717A',
      'soft-muted': '#A1A1AA',
      line: '#ECECEE',
      'line-soft': '#F2F2F4',
      live: '#22C55E',
      // accent wird per CSS-Variable gesetzt (Vendor-spezifisch)
      accent: 'var(--accent, #0A0A0B)',
    },
    fontFamily: {
      sans: ['"Plus Jakarta Sans"', '"Anuphan"', 'sans-serif'],
    },
    borderRadius: {
      'xl': '28px',
      'lg': '22px',
      'md': '16px',
      'sm': '12px',
      // 'full' (9999px) ist Tailwind-Standard für Pills
    },
    fontSize: {
      'display': ['28px', { lineHeight: '1.1', letterSpacing: '-0.03em', fontWeight: '800' }],
      'display-lg': ['40px', { lineHeight: '1.1', letterSpacing: '-0.03em', fontWeight: '800' }],
      'section': ['20px', { lineHeight: '1.2', letterSpacing: '-0.02em', fontWeight: '800' }],
      'title': ['17px', { lineHeight: '1.2', letterSpacing: '-0.01em', fontWeight: '800' }],
      'body': ['15px', { lineHeight: '1.6' }],
      'label': ['13px', { lineHeight: '1.4', fontWeight: '700' }],
      'caption': ['12px', { lineHeight: '1.5', letterSpacing: '0.02em', fontWeight: '600' }],
    },
  },
}
```

### Vendor-Akzent als CSS-Variable

Im Vendor-Feed-Layout wird `--accent` gesetzt (oder fällt auf ink zurück):

```blade
{{-- Public Feed Layout --}}
<div style="--accent: {{ $vendor->accent_color ?? '#0A0A0B' }};">
  {{-- Feed content --}}
</div>
```

Alle interaktiven Elemente nutzen `bg-accent` / `text-accent` / `border-accent`.
Default ohne Vendor-Wahl: monochrom schwarz. Die Bottom-Nav bleibt immer `ink`,
nie `accent`.

---

## Out of Scope (Hackathon)

- Dark Mode
- Mehrere Akzentfarben pro Vendor (genau eine)
- Mehrere Templates (ein cleanes Template)
- Komplexe Animationen (nur Hover, Status-Glow, simple Transitions)
- Custom Icon-Set (Emoji + Unicode reichen; später Lucide/Heroicons)
- Visuelle Theme-Editor-UI (Akzent wird simpel als Color-Picker im Dashboard gesetzt)
