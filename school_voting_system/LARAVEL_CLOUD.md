# Deploy to Laravel Cloud

This app is Laravel 12 / PHP 8.2, MySQL, Vite, database queues, and a scheduler. Cloud handles HTTPS, builds, and migrations. Passkeys, uploads, mail, and PayMongo need extra setup.

Docs: [Quickstart](https://cloud.laravel.com/docs/quickstart) · [Environments](https://cloud.laravel.com/docs/environments) · [MySQL](https://cloud.laravel.com/docs/resources/databases/laravel-mysql) · [Object storage](https://cloud.laravel.com/docs/resources/object-storage) · [CLI](https://cloud.laravel.com/docs/api/cli)

Copy production values from the checklist at the bottom of `.env.example`. Never commit `.env` or paste local secrets into Cloud.

## 0. Before you start

1. Push the repo to GitHub, GitLab, or Bitbucket. `.env` must stay untracked.
2. The Laravel app is in `school_voting_system`. If the Git root is the parent `voting system` folder, pick **`school_voting_system`** as the application directory when Cloud asks (monorepo).
3. Confirm locally: `composer install`, `npm ci`, `npm run build`.
4. Use **new** production mail and PayMongo keys. Do not copy live keys from a local `.env` that may have been shared.

## 1. Create the Cloud application

**Dashboard**

1. Sign up at [cloud.laravel.com](https://cloud.laravel.com) and add a payment method.
2. Connect GitHub / GitLab / Bitbucket.
3. Create application → existing repository → this repo.
4. If prompted, set the root directory to `school_voting_system`.
5. Name the app, pick a region (same region for MySQL and object storage), create.
6. Do not click Deploy yet.

**CLI (optional)**

From `school_voting_system`:

```bash
composer global require laravel/cloud-cli
cloud auth
cloud ship
```

`cloud ship` walks through repo, region, env, database, and the first deploy.

## 2. Compute (environment canvas)

| Setting | Value |
|---|---|
| PHP | 8.2 or 8.3 |
| Node | 20+ |
| Build | Default (`composer install`, `npm ci`, `npm run build`) is fine |
| Deploy commands | Keep `php artisan migrate --force`. **Do not** add `php artisan storage:link` — that symlink does not persist on Cloud |
| Scheduler | On (election/announcement schedules, talent reminders, prune notifications) |
| Queue worker | On (`QUEUE_CONNECTION=database`) |
| PHP extensions | Enable **GD** (logo and photo compression) |

Octane is optional for the first launch.

## 3. Attach MySQL

1. Add a Laravel MySQL database on the canvas.
2. Cloud injects `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Set `DB_CONNECTION=mysql` in custom env if it is not already.

This app uses the database for sessions, cache, and queues. Do not use SQLite in production.

## 4. Attach object storage

Uploads (school logo, campaign images, candidate photos, avatars, announcements) use the `public` disk. Local files are wiped between deploys.

1. Add a Laravel Object Storage bucket.
2. Make the bucket **public**.
3. Cloud injects AWS / S3 variables. If `AWS_URL` is shown on the bucket page but not injected, copy it into custom env vars.

When `AWS_BUCKET` is set, uploads on the `public` disk go to that bucket. Add `AWS_URL` if Cloud shows it but does not inject it. Make the bucket **public**. Leave `AWS_BUCKET` empty on local XAMPP. Do not add `storage:link` as a deploy command.

## 5. Environment variables

Cloud generates `APP_KEY` if empty and injects database and bucket credentials. Add the rest from `.env.example` (Laravel Cloud / production block).

Required by this app:

```env
APP_NAME="School Voting System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-CLOUD-DOMAIN
APP_TIMEZONE=Asia/Manila
LOG_LEVEL=warning
SESSION_DRIVER=database
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_CONNECTION=mysql

PASSKEYS_RELYING_PARTY_ID=your-host.com
PASSKEYS_ALLOWED_ORIGINS=https://your-host.com
PASSKEYS_ALLOWED_AUTH_TYPES=platform
PASSKEYS_ALLOWED_USER_VERIFICATION=preferred

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

PAYMONGO_SECRET_KEY=
PAYMONGO_PUBLIC_KEY=
PAYMONGO_WEBHOOK_SECRET=
```

`PASSKEYS_RELYING_PARTY_ID` is the host only (no `https://`, no path).  
`PASSKEYS_ALLOWED_ORIGINS` is the full origin (`https://your-host.com`).

After the first deploy you will know the `*.laravel.cloud` URL. Set `APP_URL` and the two passkey vars to that host, then redeploy. When you add a custom domain, update those three again.

## 6. Deploy

Dashboard: **Deploy**.  
CLI (later deploys): `cloud deploy` or `cloud deploy --open`.

Wait for build → migrate → release. Health check: `https://YOUR-URL/up`.

Pushes to the connected branch auto-deploy.

## 7. First Super Admin

Production starts empty.

1. Open Cloud’s Artisan / command runner.
2. Create the first Super Admin (or run a production-safe seeder).
3. Open the live site over **HTTPS**.
4. Register a **new** passkey on that domain. Localhost passkeys will not work.

## 8. Custom domain and PayMongo

1. Add the school domain in Cloud and wait for SSL.
2. Point DNS as Cloud instructs.
3. Update `APP_URL`, `PASSKEYS_RELYING_PARTY_ID`, and `PASSKEYS_ALLOWED_ORIGINS`. Redeploy.
4. In PayMongo, webhook URL: `https://your-domain.com/webhooks/paymongo`  
   Events: `checkout_session.payment.paid`, `payment.paid`, `payment.failed`.

## 9. After it is live

- [ ] Passkey login on HTTPS (not IP, not `http://`)
- [ ] System Settings school logo still shows after a second deploy
- [ ] Test enrollment email
- [ ] One test donation
- [ ] A scheduled election opens
- [ ] Queue jobs leave the `jobs` table (mail)

## What breaks if skipped

| Skip | Result |
|---|---|
| Wrong app directory | Composer / Vite not found |
| No object storage | Logos and photos disappear after deploy |
| `storage:link` as a deploy command | Link does not persist |
| Passkey env still `localhost` | Sign-in fails on the live site |
| `APP_DEBUG=true` | Stack traces in production |
| No worker / scheduler | Mail, election open/close, announcements stall |
| No GD | Image uploads store poorly or fail |
| Pasting local `.env` | Wrong DB, leaked keys, localhost passkeys |
