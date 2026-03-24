# WPS-SME — Deployment on Plesk

This document describes how to deploy the **WPS-SME** Laravel application (inside `wps-app/`) on **Plesk** with PHP and MySQL.

---

## 1. Requirements

- **Plesk** (with PHP and MySQL/MariaDB support)
- **PHP** 8.2+ with extensions: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- **MySQL** 5.7+ or **MariaDB** 10.3+
- **Composer** (on the server or deploy via Git + run composer on server)

---

## 2. Create database in Plesk

1. In Plesk: **Databases** → **Add Database**.
2. Create a new database (e.g. `wps_sme`) and a database user with full privileges.
3. Note the **database name**, **user**, and **password** for `.env`.

---

## 3. Deploy the application

### Option A: Git deployment

1. In Plesk: **Domains** → your domain → **Git**.
2. Clone the repository (or connect your Git and pull).
3. Set the **document root** to: `wps-app/public`  
   (Plesk: **Hosting & DNS** → **Hosting settings** → Document root: `wps-app/public`).
4. In **Deploy** or **Additional actions**, run:
   - `cd wps-app && composer install --no-dev --optimize-autoloader`
   - `php artisan key:generate` (if key not set)
   - `php artisan migrate --force`
   - `php artisan db:seed --force`
   - `php artisan storage:link` (optional; only if you use public storage)
   - `php artisan config:cache`
   - `php artisan route:cache`

### Option B: Upload files

1. Upload the project so that the Laravel app is in a folder (e.g. `wps-app`) under the domain root.
2. Set document root to `wps-app/public`.
3. SSH or use Plesk’s “Run Command” to run the same commands as in Option A from the `wps-app` directory.

---

## 4. Environment (.env) on Plesk

Create or edit `wps-app/.env` (or use Plesk’s “Environment variables” if it maps to `.env`):

```env
APP_NAME="WPS-SME"
APP_ENV=production
APP_KEY=base64:XXXX   # generate with php artisan key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=wps_sme
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

Replace `yourdomain.com`, `wps_sme`, `your_db_user`, and `your_db_password` with your actual values.

---

## 5. Permissions

Ensure the web server can write to Laravel’s directories:

- `wps-app/storage` — chmod 775 (or 755) and ensure the PHP/web user owns or can write to it.
- `wps-app/bootstrap/cache` — chmod 775 (or 755).

On Linux (run from `wps-app`):

```bash
chmod -R 775 storage bootstrap/cache
# If needed, set owner to the web server user, e.g.:
# chown -R www-data:www-data storage bootstrap/cache
```

Plesk often uses `nginx` or `apache` and a dedicated user; use that user for `chown` if required.

---

## 6. Post-deployment

1. **Migrations and seed:**  
   `php artisan migrate --force`  
   `php artisan db:seed --force`

2. **Caches:**  
   `php artisan config:cache`  
   `php artisan route:cache`
   `php artisan view:cache`

3. **Admin login:**  
   Default seeded admin: **admin@wps-sme.local** / **password**.  
   Change this in production (edit user in DB or add a “change password” flow).

4. **API for external sites:**  
   - Base URL: `https://yourdomain.com/api`  
   - Login: `POST /api/login` with `email` and `password` → returns `token`.  
   - Use header: `Authorization: Bearer <token>` for `/api/v1/*` routes (orders, wallet, plans, etc.).

---

## 7. Database backup (Plesk)

- Use **Plesk** → **Databases** → your database → **Backup** / **Scheduled backups** to back up the MySQL database regularly.

---

## 8. Optional: cron for Laravel

If you add scheduled tasks later:

- In Plesk: **Scheduled Tasks** (cron).
- Add: `* * * * * cd /var/www/vhosts/yourdomain.com/wps-app && php artisan schedule:run >> /dev/null 2>&1`  
  (adjust path to your `wps-app` directory.)

---

## 9. Security checklist

- [ ] `APP_DEBUG=false` in production.
- [ ] Strong `APP_KEY` set.
- [ ] Database user has only required privileges.
- [ ] HTTPS enabled in Plesk (SSL/TLS).
- [ ] Default admin password changed.
- [ ] `.env` not under document root (it is in `wps-app/`, not in `public/`).
- [ ] Run `php artisan config:cache` after changing `.env`.

---

## 10. Project structure (reference)

- Application code: `wps-app/`
- Document root: `wps-app/public`
- Config: `wps-app/.env`
- Database migrations: `wps-app/database/migrations`
- Seeders: `wps-app/database/seeders`
