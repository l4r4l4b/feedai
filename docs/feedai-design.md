# FeedAI — Design System

Visuelle Sprache, Tokens und Komponenten-Prinzipien für FeedAI.
Mobile-first, warm-neutral, content-zentriert. UI bleibt leise, damit
Vendor-Content laut sein kann.

---

## Design-Prinzipien

1. **Content first.** Vendor-Fotos, Preise, Worte sind laut. UI bleibt leise.
2. **Warm neutrals only.** Keine harten Tech-Blaus, keine Purple-Gradients. Palette atmet wie Papier.
3. **One accent.** CTAs sind fast schwarz. Farbe ist Werkzeug, keine Dekoration.
4. **Generous space.** Padding statt Dekoration. Whitespace ist das Design.
5. **Mobile first, always.** Jede Komponente vom Phone aus gedacht. Desktop ist Nebeneffekt.
6. **AI invisible.** Keine Sparkles, keine Gradients, keine "Magie". AI ist ein Werkzeug das der Vendor nie sehen muss.

---

## Color Tokens

### Backgrounds

| Token    | Hex       | Verwendung                                                |
| -------- | --------- | --------------------------------------------------------- |
| `canvas` | `#FAFAF7` | Haupt-Hintergrund, leicht warm getönt, kein steriles Weiß |
| `card`   | `#FFFFFF` | Karten und Komponenten, hebt sich minimal vom Canvas ab   |
| `soft`   | `#F2F0EA` | Sekundäre Bereiche, Footer, Input-Felder im Ruhezustand   |
| `soft-2` | `#E8E5DD` | Akzent-Soft, Hover-States, Sub-Sections                   |

### Text

| Token      | Hex       | Verwendung                               |
| ---------- | --------- | ---------------------------------------- |
| `ink`      | `#1A1A17` | Primärer Text. Fast schwarz, leicht warm |
| `muted`    | `#6B6963` | Sekundärer Text, Labels, Captions        |
| `soft-ink` | `#A8A59E` | Placeholder, disabled-States             |

### Accent

| Token    | Hex       | Verwendung                                                     |
| -------- | --------- | -------------------------------------------------------------- |
| `accent` | `#2E2B26` | CTAs, primary Buttons. Bewusst neutral-dunkel statt farbig     |
| `warm`   | `#C5705D` | Selten genutzte Highlights, "Live" Badges, kleine Markierungen |

### Functional

| Token     | Hex       | Verwendung                                  |
| --------- | --------- | ------------------------------------------- |
| `success` | `#5B7D5F` | Erfolg, "Live", positive Status-Indikatoren |
| `warning` | `#C99650` | Warnungen, fehlende Inhalte                 |
| `error`   | `#B5564D` | Fehler, kritische Hinweise                  |

### Lines & Borders

| Token  | Hex       | Verwendung                               |
| ------ | --------- | ---------------------------------------- |
| `line` | `#EAE7DF` | Trennlinien, Card-Borders, Input-Borders |

---

## Typografie

### Schriften

- **IBM Plex Sans** — Hauptschrift, Body und Headlines
- **IBM Plex Sans Thai** — Thai-Glyphen
- **IBM Plex Mono** — Technische Labels, Metadaten, Sprach-Toggles, Status-Codes

Geladen via Google Fonts. OFL-Lizenz, kostenfrei.

### Gewichte

- **Light (300)** — sparsam, nur für sehr große Display-Headlines wenn nötig
- **Regular (400)** — Body, langer Text
- **Medium (500)** — Headlines, Labels, Buttons, alles was hervorgehoben wird
- **Semibold (600)** — sparsam, nur für richtig wichtige Anker (Hero-Titel)

### Skala

| Stufe   | Größe                             | Gewicht | Letter-Spacing | Line-Height | Verwendung                      |
| ------- | --------------------------------- | ------- | -------------- | ----------- | ------------------------------- |
| Hero    | 28–32px (mobile) / 40px (desktop) | 600     | -0.02em        | 1.1         | Vendor-Name, Page-Titles        |
| Section | 18–22px                           | 500     | -0.01em        | 1.25        | Sektionen, Card-Headlines       |
| Body    | 15–16px                           | 400     | 0              | 1.55        | Beschreibungen, About-Text      |
| Caption | 12–13px                           | 400     | 0              | 1.5         | Meta-Info, Subtexte             |
| Mono    | 10–11px                           | 500     | 0.05–0.12em    | 1.4         | UPPERCASE für technische Labels |

### Regeln

- Generell **eine Schrift, zwei Gewichte** pro Komponente
- Mono nur für **technische Marker** (Status, Codes, Sprach-Toggles), nie für Body
- Body bekommt großzügige Line-Height (1.5–1.65) für Lesbarkeit
- Headlines kompakter (1.1–1.25) für visuellen Anker

---

## Spacing

4px-Basis (Tailwind default). Großzügig nutzen.

| Token | Wert    | Verwendung                            |
| ----- | ------- | ------------------------------------- |
| `xs`  | 4px     | Inline-Gaps, kleine Inset             |
| `sm`  | 8px     | Item-Spacing in Listen                |
| `md`  | 12px    | Innerhalb von Cards, zwischen Buttons |
| `lg`  | 16–20px | Card-Padding, Section-Inset           |
| `xl`  | 24–32px | Section-Padding vertikal              |
| `2xl` | 40–48px | Page-Padding, große Trennungen        |

**Section Spacing zwischen Komponenten im Feed:** 28px vertikal als Default.

---

## Radien

| Token           | Wert   | Verwendung                  |
| --------------- | ------ | --------------------------- |
| `radius-card`   | 16px   | Karten, größere Komponenten |
| `radius-image`  | 12px   | Bilder, Image-Container     |
| `radius-button` | 12px   | Buttons, CTAs               |
| `radius-input`  | 10px   | Input-Felder, Selects       |
| `radius-icon`   | 8–10px | Kleine Icon-Container       |
| `radius-pill`   | 999px  | Toggles, Badges, Avatar     |

---

## Schatten

Sehr subtil oder gar keine. Flat statt floating.

- Karten (optional): `0 1px 3px rgba(0, 0, 0, 0.04)`
- Phone-Frame im Mockup: `0 1px 2px rgba(0,0,0,0.02), 0 8px 24px rgba(20, 18, 14, 0.04)`
- Status-Dots: `0 0 0 4px rgba(color, 0.15)` für sanftes Glow

---

## Bilder

### Aspect Ratios (konsequent durchhalten)

| Verwendung          | Ratio        | Beispiel                     |
| ------------------- | ------------ | ---------------------------- |
| Hero                | 4:5          | Vendor-Foto, Auto, Stand     |
| Service / Card-Bild | 16:9         | Tour-Vorschau, Service-Cover |
| Menu / Product      | 1:1          | Einzelne Items               |
| Image Divider       | 21:9         | Volle Breite, atmosphärisch  |
| Gallery-Item        | 1:1 oder 4:5 | Konsistent pro Gallery       |

### Regeln

- **Keine automatischen Filter** oder Tönungen auf Vendor-Bilder. Sie bleiben wie sie sind.
- Volle Breite oder fast volle Breite. Bilder sollen "atmen".
- Lazy-Loading per Default.
- Spatie Media Library handled Konvertierungen (webp, responsive sizes).

---

## Komponenten

### Hero

- **Layout:** Bild 4:5, darunter Titel, darunter Subtitle
- **Typo:** Titel Hero-Stufe, Subtitle Body in `muted`
- **Padding:** 20–24px Bild-Padding nach unten zu Titel
- **Optional:** Date/Location-Badge oben links auf Bild (Mono, white-bg-blur)

### About

- **Layout:** Optional Section-Title in Mono drüber, dann Body-Text
- **Typo:** Body 15px, line-height 1.65
- **Max-width:** auf Desktop bei 70ch begrenzen für Lesbarkeit

### Service Card

- **Hintergrund:** `soft`
- **Border:** `line`
- **Padding:** 20px
- **Aufbau:** Bild 16:9 → Titel → Meta-Row (Info + Preis) → CTA-Button
- **CTA:** `accent`-Hintergrund, voll breit

### Image Divider

- **Layout:** Bild volle Breite (über Card-Padding hinaus), 21:9
- **Overlay:** Gradient von unten (`rgba(0,0,0,0.6)` → transparent)
- **Text:** Weiß, unten-links positioniert
- **Headline:** 20px Medium
- **Sub:** 13px, opacity 0.85

### Highlight Card

- **Layout:** Icon-Box + Content nebeneinander
- **Icon-Box:** 40×40px, `soft`-Hintergrund, Radius 10px
- **Border:** `line`
- **Padding:** 18×20px

### Contact Buttons

- **Layout:** 2-spaltiges Grid auf Mobile
- **Aufbau:** Icon (24×24, Channel-Farbe) + Label
- **Hover:** Border verstärkt sich, Background `soft`
- **Channel-Farben:**
  - WhatsApp `#25D366`
  - LINE `#06C755`
  - Facebook `#1877F2`
  - Call `ink`

### Contact Form

- **Hintergrund:** `soft`
- **Inputs:** `card`-Hintergrund mit `line`-Border, Radius 10px
- **CTA:** `accent` voll breit
- **Footnote:** Mono 10px in `soft-ink`, erklärt Translation explizit

### Pay Now CTA

- **Hintergrund:** `accent` (fast schwarz)
- **Text:** `card`-Weiß
- **Layout:** Label (Mono uppercase) + Title + Arrow-Icon rechts
- **Arrow:** 36×36px, `rgba(255,255,255,0.12)` Hintergrund, Radius 999px

### Image with Text (generisch)

- **Layout:** Bild 16:9 oder 4:3 oben, Text darunter
- **Padding:** Text mit 16px Abstand zum Bild
- **Optional:** Headline (Section-Stufe) über dem Text

### Text Block (generisch)

- **Optional:** Headline in Section-Stufe
- **Body:** 15px, line-height 1.65
- **Padding:** vertikal 24px

### Highlight Card mit Icon (generisch)

- siehe Highlight Card oben
- Icon kann Emoji oder einfaches Symbol sein

---

## Dashboard-Komponenten

### Component-Item (in Komponenten-Liste)

- **Background:** `card`
- **Border:** `line`, im aktiven State `ink`
- **Padding:** 14×16px
- **Aufbau:** Icon-Box (32×32, `soft`-bg) + Name/Meta + State-Badge

### State Badge

- **Format:** Mono 10px, uppercase, padding 4×8px, radius 4px
- **States:**
  - `Filled` → grün-Tint `rgba(91, 125, 95, 0.08)`, Text `success`
  - `Missing X` → warm-Tint `rgba(197, 112, 93, 0.08)`, Text `warm`

### AI Chat

- **Background:** `canvas` (heller als Card)
- **Bubble AI:** `card` bg, `line` border, radius 14px (border-bottom-left 4px)
- **Bubble User:** `accent` bg, `card` text, margin-left auto
- **Input:** Pill-Shape (radius 999px), `card` bg, `line` border
- **Send-Button:** 32×32 `accent`, radius 999px

### Status-Indikator (Dashboard-Header)

- **Dot:** 8×8px, `success`-Farbe, mit Glow
- **Text:** Inline mit Status + Meta + Action-Link

---

## Logo / Wortmarke

`Feed` in `ink`, `AI` in `soft-ink`, beides IBM Plex Sans Semibold, Letter-Spacing -0.02em.

Begründung: Das tieferstellen des `AI`-Suffix macht aus dem Tool kein "AI-Tool" als Verkaufsargument, sondern positioniert AI als unsichtbares Werkzeug — passend zur Vision dass der Vendor nie merken soll dass er gerade mit KI arbeitet.

Klein/Großschreibung: Logo als `FeedAI` (CamelCase). In Body-Text als FeedAI.

---

## Sprach-Spezifika

### Thai-Rendering

- **Font:** IBM Plex Sans Thai
- **Line-Height:** etwas mehr als bei Latin (1.6–1.7 statt 1.5–1.55), weil Thai-Glyphen vertikal mehr Platz brauchen
- **Letter-Spacing:** 0 (kein negatives Spacing wie bei Latin-Headlines)
- **Bei Mischtext:** Stack die Schriften korrekt im CSS — `font-family: 'IBM Plex Sans', 'IBM Plex Sans Thai', sans-serif;`

### Sprach-Toggle

- **Position:** Top-Bar, rechts
- **Form:** Pill mit 3 Optionen (TH / EN / DE), aktive Option mit `card`-Hintergrund
- **Typo:** IBM Plex Mono 10px uppercase

---

## Tailwind Config (Anhang)

```js
// tailwind.config.js — relevante Custom Tokens

theme: {
  extend: {
    colors: {
      canvas: '#FAFAF7',
      card: '#FFFFFF',
      soft: '#F2F0EA',
      'soft-2': '#E8E5DD',
      ink: '#1A1A17',
      muted: '#6B6963',
      'soft-ink': '#A8A59E',
      accent: '#2E2B26',
      warm: '#C5705D',
      success: '#5B7D5F',
      warning: '#C99650',
      error: '#B5564D',
      line: '#EAE7DF',
    },
    fontFamily: {
      sans: ['"IBM Plex Sans"', '"IBM Plex Sans Thai"', 'sans-serif'],
      mono: ['"IBM Plex Mono"', 'monospace'],
    },
    borderRadius: {
      'card': '16px',
      'image': '12px',
      'button': '12px',
      'input': '10px',
    },
    fontSize: {
      'hero': ['30px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '600' }],
      'hero-lg': ['40px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '600' }],
      'section': ['18px', { lineHeight: '1.25', letterSpacing: '-0.01em', fontWeight: '500' }],
      'body': ['15px', { lineHeight: '1.65' }],
      'caption': ['13px', { lineHeight: '1.5' }],
      'mono-label': ['11px', { lineHeight: '1.4', letterSpacing: '0.08em', fontWeight: '500' }],
    },
  },
}
```

---

## Out of Scope (Hackathon)

- Dark Mode
- Vendor-Individualisierung (eigene Farben, eigenes Logo wird nur als File abgelegt, nicht visuell editiert)
- Mehrere Templates (ein cleanes Template ist genug)
- Animationen/Transitions außer Hover und Status-Glow
- Custom Icons-Set (Emoji + Unicode reichen, später ggf. Lucide oder Heroicons)
