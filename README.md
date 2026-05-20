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
