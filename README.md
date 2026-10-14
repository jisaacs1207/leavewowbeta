# leavewowbeta.live — Forever Beta Petition

PHP + MySQL petition site asking Blizzard to keep **World of Warcraft: Forever** beta online until the **November 4** launch (instead of taking it down **October 21**).

## Stack

- PHP 8.1+ (works on typical Plesk PHP 8.x)
- MySQL 5.7+ / MariaDB 10.3+
- Cloudflare Turnstile (server-side verified)
- Apache `.htaccess` (Plesk default)

**Document root:** `public/`  
**App root (config, includes, storage):** repository root

## Features

- Brand-first landing page + sign form (email + character name required; realm optional)
- One email = one signature (unique hash)
- Public recent signatures show **character name only** (never email)
- Spam: honeypot + IP rate limiting + Cloudflare Turnstile
- Shareable URL, signature count, privacy note

---

## Quick deploy overview

DNS for `leavewowbeta.live` is already on Cloudflare → Plesk. You still need:

1. GitHub repo connected to Plesk (or Actions secrets)
2. MySQL database + schema
3. `.env` with DB + Turnstile keys
4. Document root pointed at `public/`

Prefer **path A** (Plesk Git). Use **path B** (GitHub Actions) only if you want deploy from GitHub without Plesk Git UI.

---

## Path A — Plesk built-in Git (recommended)

1. Push this repo to GitHub (`jisaacs1207/leavewowbeta`).
2. In **Plesk** → your domain `leavewowbeta.live` → **Git**:
   - Add repository → `https://github.com/jisaacs1207/leavewowbeta.git`
   - Branch: `main`
   - Deploy directory: typically `/httpdocs` (repo root lands here)
   - Enable **automatic deployment** (webhook) so each push to `main` pulls.
3. **Additional deployment actions** (optional but useful) — paste:

   ```bash
   bash deploy/plesk-post-deploy.sh
   ```

4. **Hosting Settings** → set **Document root** to the `public` directory  
   Example: `/var/www/vhosts/leavewowbeta.live/httpdocs/public`
5. Create DB + `.env` (see below). Schema is not auto-run — import `sql/schema.sql` once.
6. Push to `main` → Plesk pulls → site updates.

> Keep `.env` **only on the server**. Never commit it. Plesk Git deploy will not overwrite an existing untracked `.env` if you create it outside the repo or mark it as a server-only file.

---

## Path B — GitHub Actions SFTP / SSH (optional)

Workflow: `.github/workflows/deploy-petition-plesk.yml`

It runs on pushes to `main`. **Without secrets it skips** (safe no-op).

### Option B1 — SFTP secrets

| Secret | Example |
|--------|---------|
| `PLESK_SFTP_HOST` | `leavewowbeta.live` or server IP |
| `PLESK_SFTP_USER` | Plesk system/FTP user |
| `PLESK_SFTP_PASSWORD` | password |
| `PLESK_SFTP_PORT` | `22` (optional) |
| `PLESK_REMOTE_PATH` | `/httpdocs` (optional; repo root) |

### Option B2 — SSH + rsync secrets

| Secret | Example |
|--------|---------|
| `PLESK_SSH_HOST` | server hostname/IP |
| `PLESK_SSH_USER` | SSH user |
| `PLESK_SSH_KEY` | private key PEM |
| `PLESK_SSH_PORT` | `22` (optional) |
| `PLESK_REMOTE_PATH` | absolute path to `petition/` on server |

After first Actions deploy, still set document root to `…/petition/public` and create `.env` on the server (workflow excludes `.env`).

---

## Database setup

1. Plesk → **Databases** → Add database (e.g. `leavewowbeta`) + user with full rights.
2. Import schema:

   ```bash
   mysql -u DB_USER -p DB_NAME < sql/schema.sql
   ```

   Or use **phpMyAdmin** → Import → `sql/schema.sql`.

3. Confirm table `signatures` exists.

---

## Environment (`.env`)

```bash
cp .env.example .env
# edit .env in the repo root on the server — never commit this file
```

| Variable | Purpose |
|----------|---------|
| `DB_HOST` | Usually `localhost` on Plesk |
| `DB_PORT` | `3306` |
| `DB_NAME` / `DB_USER` / `DB_PASS` | From Plesk Databases |
| `TURNSTILE_SITE_KEY` | Cloudflare Turnstile widget site key |
| `TURNSTILE_SECRET_KEY` | Cloudflare Turnstile secret (server only) |
| `APP_URL` | `https://leavewowbeta.live` |
| `APP_DEBUG` | `0` in production |
| `RATE_LIMIT_MAX` | Max sign attempts per IP per window (default `5`) |
| `RATE_LIMIT_WINDOW_SECONDS` | Window length (default `3600`) |

Ensure `storage/rate_limits` is writable by the PHP user:

```bash
mkdir -p storage/rate_limits
chmod 750 storage storage/rate_limits
```

---

## Cloudflare Turnstile walkthrough

1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com/).
2. Go to **Turnstile** (account-level; not only the DNS zone).
3. **Add widget**:
   - Name: `leavewowbeta petition`
   - Hostname: `leavewowbeta.live` (and `www` if used)
   - Mode: **Managed** (recommended)
4. Copy **Site Key** → `TURNSTILE_SITE_KEY` in `.env`.
5. Copy **Secret Key** → `TURNSTILE_SECRET_KEY` in `.env`.
6. Save `.env` on the server; reload PHP-FPM if Plesk caches env (usually not needed for file-based `.env`).
7. Open the site → sign form should show the Turnstile widget.
8. Test a signature; failed secret/site key pairs return a verification error from `/api/sign.php`.

**Test keys** (Cloudflare docs): use only in staging — never for production.

If Turnstile keys are empty, the page shows a warning and signing will fail server-side until configured (by design).

---

## Local smoke test (optional)

```bash
# With PHP + MySQL available:
cp .env.example .env
# fill DB_* (and optionally Turnstile test keys)
mysql -u root -p -e "CREATE DATABASE leavewowbeta; ..."
mysql -u root -p leavewowbeta < sql/schema.sql
php -S 127.0.0.1:8080 -t public
```

Visit `http://127.0.0.1:8080`.

---

## Privacy & legitimacy

- Emails stored for petition verification/delivery only; not sold; not shown publicly.
- Character names appear in the recent signatures list.
- Independent community petition — not affiliated with Blizzard Entertainment.
