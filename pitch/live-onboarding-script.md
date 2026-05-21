# Live Onboarding Demo Script

A copy-paste-ready vendor persona for showing the onboarding chat end-to-end on stage. Pick **one message per phase**, paste it into the chat, watch the preview iframe assemble the feed in real time. The whole flow takes ~3–5 minutes if the queue worker is up.

## Persona

**Mango Som** — a third-generation tropical fruit shake cart parked next to Terminal 21 on Sukhumvit Soi 21, Bangkok. Distinct enough from the five seeded personas (coffee, tuk-tuk, massage, walking tour, boats) that the audience sees real-time content generation, not a re-render of stored data.

## Pre-demo checklist

1. Logged out — open an Incognito window so the audience sees the registration flow.
2. Queue worker running (`worker-833310` daemon on Forge, or `sail artisan queue:work` locally). Without it, translations won't kick off in the background.
3. Open `/` first to show the marketing surface, then click **Become a vendor**.
4. Register with any email + the slug `mango-som`. The agent picks up from there.

---

## Phase 0 — Identity

Agent asks: *"Hi! 👋 What's your business called, and what exactly do you do?"*

> Mango Som. I run a tropical fruit shake cart next to Terminal 21 on Sukhumvit Soi 21. We do mango sticky rice, fresh tropical shakes, cold-pressed juice. My grandmother started the cart in 1968, I'm the third generation.

*What to highlight:* the agent calls `initializeVendorFeed`, the empty preview frame on the left springs to life with the bare skeleton.

---

## Phase 1 — Hero & Location

Agent asks: *"Lovely! Where exactly is the cart parked, and is there a photo I can use?"*

> Right outside the Sukhumvit Soi 21 entrance to Terminal 21, the mall above Asok BTS station. Same spot every day for six years now.

*(Optional, even stronger: drop a phone photo of the cart in the chat. The vision agent picks it up, tags it as `hero`, and the preview's hero block instantly gets a real image.)*

*What to highlight:* the hero component appears in the preview with a polished magazine title generated from your raw input.

---

## Phase 2 — About story

Agent asks: *"Tell me the story briefly — how long, what makes Mango Som special?"*

> My grandmother pushed a wooden cart out of Klong Toey market in the late 60s, selling mangoes from her cousin's orchard near Chiang Mai. My mum took over in '92 and added the shakes and juices. I joined her in 2014 after quitting my office job. Same mango supplier we've used for thirty years. The trick to the sticky rice is the coconut milk goes in warm and the mango stays cold — that contrast is everything.

*What to highlight:* the agent condenses three generations of family history into a 3–5 sentence magazine-voice About block. No em-dashes, no AI tells, sensory verbs.

---

## Phase 3 — Offerings

Agent asks: *"What are your 3–5 main items? Name + price + a quick line about each."*

> Four things:
>
> 1. Mango Sticky Rice — 80 THB. The original. Warm coconut sticky rice with two halves of nam dok mai mango. Sesame on top.
> 2. Tropical Shake — 70 THB. Pick your fruit: mango, banana, pineapple, or dragon fruit. Blended with ice and a touch of condensed milk.
> 3. Cold-Pressed Juice — 90 THB. Pineapple-ginger or watermelon-mint. Squeezed in front of you, no sugar added.
> 4. Coconut Smoothie Bowl — 120 THB. Young coconut, frozen banana, granola, chia. About 3 minutes prep, eat with a spoon while it's cold.

*What to highlight:* one `fillComponent('menu')` call. The agent picks `section_label: 'MENU'`, structures each item with name/price/description in magazine voice, and the menu grid populates in the preview with all four items at once.

---

## Phase 4 — Opening hours

Agent asks: *"When are you open?"*

> Open every day except Wednesday. 9am to 9pm Monday through Saturday. Sundays we close at 6pm because the office crowd is gone.

*What to highlight:* the opening hours component renders structured rows (Mon–Sat 09:00–21:00, Sun 09:00–18:00, Wed closed) — not just a copy-paste of the input.

---

## Phase 5 — Contact channels

Agent asks: *"How can guests reach you?"*

> WhatsApp is the easiest: +66812223344. We're also on LINE, handle `mango.som`. Most people message us 10–15 minutes before walking over so the sticky rice is ready when they arrive.

*What to highlight:* contact buttons appear with proper channel icons (WhatsApp green, LINE) and click-to-message links.

---

## Phase 6 — Review & finalize

Agent recaps the feed in one short sentence and asks: *"Should I take it live now?"*

> Yes, take it live!

*What to highlight:*

- Three components auto-fill in the same turn: **CTA**, **pay-now trigger**, **contact form**.
- `finalizeOnboarding` flips the vendor's status to `live`.
- The agent's last text reply mentions payments-setup is next, and the platform redirects to `/dashboard/payments`.
- In the background: a `TranslateComponent` job per component goes to the queue. ~30–60 seconds later, `/{slug}?lang=de` and `/{slug}?lang=th` are populated.

---

## What to show next (post-onboarding)

1. **Open `/mango-som` in a new tab** — the public feed is live, mobile-frame styled, no marketing copy needed.
2. **Switch the locale picker to DE** — even if translations are still queuing, the UI surface (Pay, Message, Back to top) is already in German.
3. **Once translations land (≤60 s)** — DE and TH content shows up.
4. **Open `/mango-som/pay`** — three rails: card, PromptPay QR, crypto. Card opens real Stripe test checkout in demo mode.
5. **Open `/mango-som/contact?lang=de`** — type a German message, the vendor (back in the dashboard inbox) reads it in English.

## If something hangs

- **Translation not appearing:** queue worker isn't running. Restart `worker-833310` daemon.
- **No image on hero block:** vendor didn't drop a photo. Run with text-only, the hero still renders with a placeholder.
- **Agent skips a phase:** rare, but if the system prompt is cached old, run `php artisan view:clear && php artisan config:clear`.

## Total time on stage

- Onboarding chat: ~3 minutes (six pastes, ~30 seconds each for agent to respond)
- Locale + payment + contact bridge walkthroughs: ~2 minutes
- Total: **5 minutes of live demo material**.
