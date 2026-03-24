# WPS-SME deployment (Plesk / httpdocs)

## Directory and document root

- Upload the **entire** Laravel app (e.g. into `httpdocs/wps-app/`).
- In Plesk, set the **document root** to the Laravel `public` folder:
  - `httpdocs/wps-app/public`
- The site URL should be the domain root, e.g. `https://western-paypoint.online` (no path like `/wps-app`).

## .env on the server

Set at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://western-paypoint.online
```

(No trailing slash on `APP_URL`.)

Then:

```bash
cd /path/to/wps-app
php artisan config:clear
php artisan config:cache
```

## If you get 500 on a request (e.g. POST /admin/notifications)

1. **See the real error**  
   On the server, open:
   ```text
   wps-app/storage/logs/laravel.log
   ```
   Scroll to the **bottom** and read the latest stack trace. It will show the exact exception (e.g. database, missing table, permission, wrong path).

2. **Check document root**  
   If the document root is not `.../wps-app/public`, Laravel may not run correctly. All requests must go through `public/index.php`.

3. **Check permissions**  
   Ensure the web server can write to:
   - `storage/` (and subdirs)
   - `bootstrap/cache/`

4. **After changing .env**  
   Run `php artisan config:cache` (and optionally `php artisan cache:clear`) so the app uses the new values.

Once you have the exact error from `laravel.log`, you can fix the underlying cause (e.g. missing migration, wrong DB credentials, or path).

## If you get 419 (Page Expired) on POST requests

419 means Laravel’s CSRF check failed. Usually the **session cookie is missing or wrong** when the form is submitted. On HTTPS this often happens if the session cookie wasn’t set as “Secure”.

1. **Set `APP_URL` to your real HTTPS URL** (already required above):
   ```env
   APP_URL=https://western-paypoint.online
   ```
   The app now turns on the **Secure** session cookie automatically when `APP_URL` starts with `https://`, so the browser will send the cookie on POST.

2. **If you still get 419**, set the secure cookie explicitly:
   ```env
   SESSION_SECURE_COOKIE=true
   ```
   Then run `php artisan config:clear` and `php artisan config:cache`.

3. **Session driver and storage**
   - If using `SESSION_DRIVER=file`, ensure the server can write to `storage/framework/sessions`.
   - Do not use `SESSION_DOMAIN` unless you need cross-subdomain cookies; leave it unset or `null`.

4. **Avoid long-lived cached config**
   After any change to `APP_URL` or session-related env vars, run:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

5. **User-side**
   - Ask the user to do a hard refresh (Ctrl+F5) on the page with the form, then submit again (so the form and CSRF token are fresh).
   - If they had the form open for a long time, the session may have expired; they should open the page again and resubmit.

## Before you ZIP / upload (on your PC)

1. **Composer** (dependencies):
   ```bash
   composer install --optimize-autoloader --no-interaction
   ```
   On production servers, prefer `--no-dev` after upload.

2. **Front-end build** (required for `public/build`; this folder is in `.gitignore`, so include it in your archive if the server has no Node.js):
   ```bash
   npm install
   npm run build
   ```

3. **Optional local checks** (needs working DB credentials in `.env`):
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize:clear
   ```

## Right after upload (on the server, SSH or Plesk “PHP” terminal)

From the app root (e.g. `httpdocs/wps-app`):

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link --force
php artisan optimize
```

- **First install only** (empty database): run once after migrate:
  ```bash
  php artisan db:seed --force
  ```
  Do **not** re-run full `db:seed` on a live database if it would reset roles/users; use targeted seeders if you only need new permissions/settings.

- If `migrate` fails, fix **`.env`** `DB_*` on the server, then run `php artisan migrate --force` again.

- After any **`.env`** change: `php artisan config:clear` then `php artisan config:cache` (or `php artisan optimize`).

## No SSH / no terminal (run migrations over HTTPS)

If the host **denies SSH** and you have **no** cron/terminal tool, you can run migrations **once** via a secret URL (already built into this app).

1. On your PC, generate a long random token (64 hex chars is fine):
   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

2. On the server, edit **`.env`** (Plesk **File Manager** or FTP) and add:
   ```env
   DEPLOY_MIGRATION_TOKEN=paste_the_generated_value_here
   ```
   Ensure **`DB_*`** in the same `.env` is correct for the server database.

3. If you previously ran **`php artisan config:cache`** on the server, delete the files in **`bootstrap/cache/`** (except `.gitignore`) so the new env value is read — or ask the host to clear them.

4. **Do not paste the URL into the browser bar** — that sends **GET** and will not run migrations. From your PC, send **one POST** (replace domain and token):

   ```bash
   curl -sS -X POST "https://YOUR-DOMAIN.com/api/v1/deploy/migrate" ^
     -H "Accept: application/json" ^
     -H "X-Deploy-Token: paste_the_same_token_here"
   ```
   (On macOS/Linux use `\` instead of `^` for line breaks.)

   You should get JSON like `{"ok":true,"output":"..."}`.

5. **Immediately remove** `DEPLOY_MIGRATION_TOKEN` from `.env` on the server (or set it empty) so the endpoint stays disabled (`404`). Optionally delete `bootstrap/cache/*.php` again if you use config caching.

**Security:** Anyone who guesses the URL and token could run migrations. Use a **long random** token, run **once**, then **remove** it. Do not share the token. Prefer SSH when your host allows it later.

## `public/` checklist for hosting

- Document root must point at **`public/`** (see top of this file).
- **`public/build/`** — present after `npm run build` (manifest + hashed CSS/JS).
- **`public/storage`** — symlink to `storage/app/public` (created by `php artisan storage:link`).
- Writable by the web user: **`storage/`**, **`bootstrap/cache/`**.
