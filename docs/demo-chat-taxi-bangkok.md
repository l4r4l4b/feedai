# Demo-Chat: Bangkok Taxi mit Touren

Vorformulierte Antworten für einen Test-Onboarding. Einfach von oben nach
unten in den Chat copypasten. Reihenfolge passt zum erwarteten Frage-Flow
des OnboardingAgents.

## Setup

1. `/register` neuen Account anlegen — Name: `Somchai Taxi Tours`, irgendeine Email
2. Du landest auf `/onboarding`
3. Antworten der Reihe nach kopieren

---

## Turn 1 — Was machst du?

```
Hallo! Ich bin Somchai, Taxifahrer in Bangkok seit 18 Jahren. Ich mache normale Fahrten vom Flughafen + private Halbtages- und Ganztagestouren durch die Stadt. Mein Mitsubishi Xpander hat Klimaanlage und passt für 4 Personen.
```

## Turn 2 — Wo ungefähr?

```
Hauptsächlich Bangkok — Sukhumvit, Silom, Chinatown, alle großen Hotels, und natürlich Suvarnabhumi Airport. Touren gehen auch raus nach Ayutthaya und zum Floating Market.
```

## Turn 4 — Preise / Touren

```
Drei Touren biete ich an:
1) Halbtagestour Tempel (4 Std, max 3 Gäste, 2.500 THB)
2) Ganztagestour Ayutthaya (8 Std, max 3 Gäste, 4.800 THB)
3) Floating Market & Mahanakhon (6 Std, max 4 Gäste, 3.500 THB)
```

## Turn 5 — Kontakt

```
WhatsApp: +66 81 234 5678
LINE: somchai-taxi
Call: +66 81 234 5678
```

## Turn 6 — Öffnungszeiten

```
Verfügbar Mo–Sa von 6:00 bis 22:00. Sonntag Ruhetag. Flughafentransfer auch nachts nach Vereinbarung.
```

## Turn 7 — FAQ

```
Häufige Fragen:
- Akzeptiere ich Kreditkarten? Ja, über die Webseite. Cash und PromptPay vor Ort.
- Englisch ok? Ja, fließend Englisch.
- Kindersitze? Auf Anfrage kostenlos.
- Storno? Bis 24h vorher kostenlos.
```

## Turn 8 — Testimonial (optional)

```
Eine schöne Bewertung von letzter Woche, kannst du das als Testimonial nehmen?
"Somchai war pünktlich, geduldig und kennt jeden Schleichweg. Ayutthaya-Tour war ein Highlight." — Familie Becker aus Hamburg
```

## Turn 9 — Bild

Klick aufs 📎 und lade ein Foto hoch (Auto, Tempel, oder dich am Steuer).
Optional Text dazu:

```
Hier ist ein Foto von meinem Wagen vorm Wat Pho.
```

Die AI sollte das als Hero-Bild oder Gallery-Item vorschlagen — je nachdem was das Bild zeigt.

## Turn 10 — Finalize

Wenn die AI fragt ob alles passt:

```
Ja, schalt es live.
```

---

## Was du auf dem Feed sehen solltest

Nach den 10 Turns sollte der Live-Preview rechts/oben enthalten:

- Hero mit Somchai-Bild + Location-Zeile "Bangkok"
- About-Text
- 3 Service-Cards (die Touren)
- Contact-Buttons (WhatsApp + LINE + Call)
- Opening Hours
- FAQ
- Testimonial Familie Becker
- Pay-Now-Trigger zur Payment-Seite

## Erwartete Tool-Calls (für Backend-Verify)

Jedes Speichern hinterlässt eine ✓-Zeile unter der AI-Nachricht im Chat:

- `initializeVendorFeed` (Turn 1 oder 2)
- `fillComponent` mehrfach (hero, about, service, service, service, contact_buttons, opening_hours, faq, testimonial)
- `finalizeOnboarding` (Turn 10)

Falls die AI was vergisst, kannst du explizit nachfragen:

```
Du hast die Öffnungszeiten noch nicht in den Feed übernommen — kannst du das nachholen?
```
