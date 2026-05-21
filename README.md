# FeedAI

A mini AI-driven feed for the micro-businesses tourists keep walking past.

FeedAI turns one conversation with an AI into a mobile-first vendor page: auto-translated for tourists (EN / DE / TH), with a built-in chat bridge so tourist and vendor can message each other in their own languages, and three direct payment rails (Stripe Connect, PromptPay QR, crypto). The platform never holds the money.

Built for the SEABW Vibe Coding Hackathon. 212/212 tests green.

---

## Stack

- **Laravel 13** · **Livewire 4** · **Flux UI 2**
- **Tailwind v4** with semantic `@theme` tokens
- **Laravel AI SDK** with Anthropic Claude Sonnet 4.6 (chat agents + vision)
- **Spatie Media Library** for vendor photos
- **Stripe Connect Express**, **PromptPay** (EMV QR), **crypto wallet** for payments
- **Symfony YAML** for on-disk vendor content
- **Pest 4** for tests
- Runs in **Laravel Sail** (Docker)

---

## Prerequisites

- Docker Desktop (or any Docker daemon)
- PHP 8.3+ locally (only needed to bootstrap `composer install` once; everything else runs through Sail)
- Composer 2
- Node 20+ (for Vite, optional if you only need the prod build)
- An **Anthropic API key** ([console.anthropic.com](https://console.anthropic.com))
- A **Stripe API key** is optional in dev — the app auto-enables demo mode when `STRIPE_SECRET` is empty.

---

## Local Setup

```bash
git clone https://github.com/l4r4l4b/feedai.git
cd feedai

cp .env.example .env

# 1. Install PHP dependencies via the local PHP binary so Sail can be bootstrapped.
composer install --ignore-platform-reqs

# 2. Start the containers (laravel.test, mysql, redis, mailpit).
./vendor/bin/sail up -d

# 3. Generate the application key inside the container.
./vendor/bin/sail artisan key:generate

# 4. Install JS deps and build assets.
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Configure the environment

Open `.env` and set at minimum:

```dotenv
APP_URL=http://localhost:8000

ANTHROPIC_API_KEY=sk-ant-...

AI_DEFAULT_PROVIDER=anthropic
AI_DEFAULT_MODEL=claude-sonnet-4-6
```

Optional for full payment flow:

```dotenv
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_DEMO_ACCOUNT_ID=acct_...
DEMO_PROMPTPAY_PHONE=0812345678
```

Leave Stripe blank and the app runs in demo-pay mode (a fake checkout that records the payment locally).

### Seed the database

```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan feedai:backfill-translations --force-sync
```

This:

- Runs all migrations.
- Creates an admin user, the legacy demo vendor, **five hand-curated example vendors** (Khao San Coffee, Niran's Tuk-Tuk, Pranee Thai Massage, Kru Vee Walking Tours, Sailom Klong Boats), plus demo inbox conversations and payments.
- Generates DE + TH translations of every demo vendor's eight-component feed by calling Claude (takes 2-4 minutes the first time).

### Run the queue worker

The vendor agents dispatch translation and image-analysis jobs to the queue. In a second terminal:

```bash
./vendor/bin/sail artisan queue:work --queue=default --tries=3 --backoff=30 --timeout=120 --memory=256
```

Without it, vendor edits won't regenerate the DE/TH copies and tourist messages won't translate.

### Open the app

```bash
./vendor/bin/sail open
```

Or visit:

- `http://localhost:8000/` — marketing welcome with the live-vendors strip
- `http://localhost:8000/demo` — the legacy demo vendor (17 components, all locales)
- `http://localhost:8000/khao-san-coffee` — themed coffee shop demo
- `http://localhost:8000/pranee-thai-massage?lang=en` — Thai-source vendor viewed in English
- `http://localhost:8000/login` — sign in to a vendor or admin account

### Demo accounts

The seeders create these accounts (password is `password` for every one of them):

| Email | Role |
| --- | --- |
| `admin@feedai.test` | Admin |
| `vendor@feedai.test` | Vendor (legacy demo) |
| `ploy@khaosancoffee.test` | Vendor (Khao San Coffee) |
| `niran@niran-tuktuk.test` | Vendor (Niran's Tuk-Tuk) |
| `pranee@pranee-massage.test` | Vendor (Pranee Thai Massage) |
| `vee@kruveewalks.test` | Vendor (Kru Vee Walks) |
| `sailom@sailom-boats.test` | Vendor (Sailom Boats) |

> **Never deploy these to a public URL without rotating the passwords first.** See the production deployment section below.

---

## Live Demo Walkthrough

Register a fresh vendor account (suggested slug: `mango-som`) and paste each block below in order. The agent walks through the six onboarding phases and the feed assembles in the preview iframe as you go. End-to-end takes ~3 minutes if the queue worker is running.

> **At least one product photo is required.** During Phase 1 or Phase 3, drop a photo into the chat (paperclip icon). The vision agent reads it, tags it as `hero` or `menu_item`, and the relevant component picks up the URL. Without an image the hero block stays text-only and looks unfinished. A mango-sticky-rice photo for the script below works perfectly. Any web image you have rights to use is fine.

**1.**

> Mango Som. I run a tropical fruit shake cart next to Terminal 21 on Sukhumvit Soi 21. We do mango sticky rice, fresh tropical shakes, cold-pressed juice. My grandmother started the cart in 1968, I'm the third generation.

**2.** (drop a photo of the cart or the food spread before sending)

> Right outside the Sukhumvit Soi 21 entrance to Terminal 21, the mall above Asok BTS station. Same spot every day for six years now.

**3.**

> My grandmother pushed a wooden cart out of Klong Toey market in the late 60s, selling mangoes from her cousin's orchard near Chiang Mai. My mum took over in '92 and added the shakes and juices. I joined her in 2014 after quitting my office job. Same mango supplier we've used for thirty years. The trick to the sticky rice is the coconut milk goes in warm and the mango stays cold, that contrast is everything.

**4.** (drop one or more product photos with this message)

> Four things:
>
> 1. Mango Sticky Rice, 80 THB. The original. Warm coconut sticky rice with two halves of nam dok mai mango. Sesame on top.
> 2. Tropical Shake, 70 THB. Pick your fruit: mango, banana, pineapple, or dragon fruit. Blended with ice and a touch of condensed milk.
> 3. Cold-Pressed Juice, 90 THB. Pineapple-ginger or watermelon-mint. Squeezed in front of you, no sugar added.
> 4. Coconut Smoothie Bowl, 120 THB. Young coconut, frozen banana, granola, chia. About 3 minutes prep, eat with a spoon while it's cold.

**5.**

> Open every day except Wednesday. 9am to 9pm Monday through Saturday. Sundays we close at 6pm because the office crowd is gone.

**6.**

> WhatsApp is the easiest: +66812223344. We're also on LINE, handle `mango.som`. Most people message us 10 to 15 minutes before walking over so the sticky rice is ready when they arrive.

**7.**

> Yes, take it live!

---

## Testing

```bash
./vendor/bin/sail artisan test --compact
```

Filter to a single file or test:

```bash
./vendor/bin/sail artisan test --compact --filter=ContactBridgeTest
```

Code style is enforced via Pint:

```bash
./vendor/bin/sail bin pint --dirty --format agent
```

---

## Project Structure (high level)

```
app/
├─ Ai/                    Agents (OnboardingAgent, EditAgent, Translator…) + tools
├─ Console/Commands/      feedai:backfill-translations command
├─ Jobs/                  AnalyzeImage, TranslateComponent, TranslateMessage
├─ Livewire/              Dashboard, Onboarding, Public surfaces
├─ Models/                User, Vendor, Conversation, Message, Payment
└─ Services/              ContentLoader, ContentWriter, VendorImageIngestor

config/feedai/
└─ component-schemas/     YAML definitions of every component type
                          (hero, about, menu, gallery, faq, …)

database/seeders/         AdminUserSeeder, DemoVendorSeeder, DemoFeedsSeeder,
                          DemoInboxSeeder, DemoPaymentsSeeder

storage/app/vendors/      Per-vendor content on disk:
                          ├─ vendor.yaml
                          ├─ pages/{slug}.yaml
                          ├─ content/{page}/{nn-component}.md
                          └─ translations/{locale}/{page}/{nn-component}.md
```

---

## Production Deployment

Tested on **Laravel Forge**. After connecting the repo:

1. Make sure the build pipeline runs `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`.
2. Set the required env vars in Forge: `ANTHROPIC_API_KEY`, `STRIPE_SECRET`, `APP_URL`, `DB_*`.
3. Run the seeders **once** by SSH:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan feedai:backfill-translations --force-sync
   ```
4. **Rotate demo passwords** immediately:
   ```bash
   php artisan tinker --execute='
   foreach (\App\Models\User::all() as $u) {
       $u->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32));
       $u->save();
   }'
   ```
5. Configure a Forge daemon for the queue worker:
   ```
   php8.4 /home/forge/<site>/artisan queue:work database --queue=default --sleep=3 --tries=3 --backoff=30 --timeout=120 --memory=256
   ```
   Make sure the underlying supervisor config has `stopwaitsecs=140` so deploys don't kill mid-LLM jobs.

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| `Symfony\Component\Yaml\Yaml not found` after a production deploy | Make sure `composer install --no-dev` is the deploy command and `symfony/yaml` is in `composer.json` `require` (it is, as of commit `20b2186`). |
| `/demo` returns 404 on production | The legacy demo's content lives in `storage/app/vendors/demo/`, which is shipped with the repo via a `.gitignore` allowlist. Pull the latest main and redeploy. |
| Vendor edits don't translate | The queue worker isn't running. Start it (see the queue worker section). |
| Stripe button does nothing | Either set `STRIPE_*` keys, or accept demo mode (button creates a fake payment row). |
| `Connection refused` on MySQL on prod | Check `DB_HOST=127.0.0.1` (not `mysql`), MySQL is running (`systemctl status mysql`), DB exists, and `php artisan config:clear` was run after `.env` edits. |
| Images on a vendor 404 | The file's `media.file_name` in DB no longer matches disk. `AnalyzeImage` re-checks and rolls back; if it pre-dates that fix, point the DB row at an existing file. |

---

## License

MIT.
