# DMRMS Troubleshooting Guide

**Digital Military Recruitment Management System**

---

## 🚀 Quick Reference

| Symptom | Most Likely Cause | Quick Fix |
|---------|-------------------|-----------|
| `ConnectionRefused :8000` | AI Service not running | `cd ai_service && venv\Scripts\activate && uvicorn app.main:app --reload --port 8000` |
| `ConnectionRefused :5432` | PostgreSQL not running | Start XAMPP → PostgreSQL |
| `Target class does not exist` | Composer autoload stale | `composer dump-autoload` |
| `SQLSTATE[08006]` | DB credentials wrong | Check `.env` database settings |
| `No application encryption key` | `APP_KEY` missing | `php artisan key:generate` |
| `500 on landing page` | Route name mismatch | Check `route()` calls in Blade files |
| `Vite manifest not found` | Assets not built | `npm run build` |
| `419 Page Expired` | CSRF token missing | Clear browser cache/cookies |
| Queue jobs not processing | Worker not running | `php artisan queue:work --tries=3` |
| AI health check fails | `.env` missing / token mismatch | Check `ai_service/.env` exists and `INTERNAL_API_KEY` matches `AI_SERVICE_TOKEN` |
| `Class "Imagick" not found` | imagick PHP extension not installed | Follow PDF-to-image setup in `docs/setup.md` §1.1 |
| AI document analysis skipped | PDF conversion unavailable | Install Imagick+GhostScript OR Poppler (see `docs/setup.md` §1.1) |

---

## 🐘 PostgreSQL Issues

### Can't connect — `SQLSTATE[08006]`

```bash
# 1. Verify PostgreSQL is running
# Check XAMPP Control Panel → PostgreSQL should show green "Running"

# 2. Check the port
psql -U postgres -p 5432 -c "SELECT 1;"

# 3. Verify the database exists
psql -U postgres -c "\l" | findstr dmrms

# 4. If database is missing, create it
psql -U postgres -c "CREATE DATABASE dmrms;"
psql -U postgres -c "CREATE USER dmrms WITH PASSWORD 'your_password';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE dmrms TO dmrms;"

# 5. Verify credentials in .env match
grep DB_ .env
```

### Lost data after restart

PostgreSQL data is stored in `C:\xampp\postgresql\data\`. If this directory is missing or corrupted:

```bash
# Reinitialize the data directory
C:\xampp\postgresql\bin\initdb.exe -D C:\xampp\postgresql\data
# Then restart PostgreSQL from XAMPP Control Panel
# Then re-create database and re-run: php artisan migrate --seed
```

---

## ⚡ Laravel Issues

### `Target class [xxx] does not exist`

```bash
composer dump-autoload
php artisan optimize:clear
```

### `Vite manifest not found`

```bash
npm install
npm run build
# If that fails, try:
npx vite build
```

### Route returns 404

```bash
# Check if the route is registered
php artisan route:list | findstr your-route-name

# Clear route cache
php artisan route:clear
```

### `php artisan serve` on a different port

```bash
php artisan serve --port=9000
# Then access: http://localhost:9000
```

---

## 🧠 AI Service Issues

### `ConnectionRefused :8000`

```bash
# 1. Navigate to the AI service directory
cd ai_service

# 2. Activate the virtual environment
venv\Scripts\activate

# 3. Start the service
uvicorn app.main:app --reload --port 8000

# 4. Verify health
# Open: http://localhost:8000/api/v1/health
# Expected: {"status": "healthy", "service": "DMRMS AI Service", ...}
```

### AI returns `401 Unauthorized`

The `INTERNAL_API_KEY` in `ai_service/.env` must match `AI_SERVICE_TOKEN` in the Laravel `.env`:

```bash
# Check Laravel .env
grep AI_SERVICE_TOKEN .env

# Check AI service .env
grep INTERNAL_API_KEY ai_service/.env

# They must be identical. Default: dmrms-internal-key-2026
```

### AI analysis fails with model errors

```bash
# Check which provider is configured
grep AI_PROVIDER .env

# If using OpenAI:
grep OPENAI_API_KEY .env          # Must be set
grep OPENAI_API_KEY ai_service/.env  # Must be set

# If using Gemini:
grep GEMINI_API_KEY .env          # Must be set
# Gemini uses the Laravel gateway directly, no Python service needed
```

### PDF documents skip AI analysis

The AI can only analyze images (JPEG, PNG, WebP). PDFs must be converted to images first.
Install either Option A (Imagick+GhostScript) or Option B (Poppler) — see `docs/setup.md` §1.1.

To verify the conversion pipeline:
```bash
# Check if Imagick is available
php -m | findstr imagick

# Check if pdftoppm is available
pdftoppm --version
```

If neither is installed, PDF documents will still be uploaded but will show as "needs_review" and require manual admin verification.

---

## 📦 Queue Worker Issues

### Jobs not processing

```bash
# 1. Verify the worker is running
# In the dev terminal, you should see output like:
# [2026-07-23 10:00:00] Processing: App\Jobs\ProcessDocumentVerification

# 2. If not running, start it:
php artisan queue:work --tries=3

# 3. Check the jobs table
php artisan tinker
> DB::table('jobs')->count();    # Pending jobs
> DB::table('failed_jobs')->count();  # Failed jobs
> exit

# 4. Retry failed jobs
php artisan queue:retry all
```

### Job fails repeatedly

Check the Laravel log for the error:
```bash
type storage\logs\laravel.log | findstr "ProcessDocumentVerification"
```

Common failure reasons:
- AI service not running → Start `uvicorn`
- File not found → Check `storage/app/public/documents/` exists
- Memory limit → Increase `memory_limit` in `php.ini`

---

## 🔐 Permission & Role Issues

### Admin can't see settings

Settings are restricted to `super_admin` role only:
```bash
# Check user's roles
php artisan tinker
> $admin = App\Models\Administrator::where('email', 'your@email.com')->first();
> $admin->getRoleNames();  # Should include 'super_admin'
> exit

# If not super_admin, assign it:
php artisan tinker
> $admin->assignRole('super_admin');
> exit
```

### Applicant can't upload documents

The middleware `CheckApplicantAccess` restricts document routes by application status.
If documents were rejected, the status should be rolled back to `submitted`.
If blocked, check the application status:
```bash
php artisan tinker
> $app = App\Models\Application::find(1);  # or the relevant ID
> $app->status;  # Should be 'submitted' or 'draft'
> exit
```

---

## 🖼️ Frontend Asset Issues

### Styles not loading / Broken layout

```bash
# Rebuild all frontend assets
npm run build

# If using the dev server, make sure Vite is running
npm run dev
```

### Chart.js not rendering

```bash
# Verify Chart.js is installed
npm list chart.js
# If missing: npm install chart.js
```

### Alpine.js components not working

```bash
# Verify Alpine.js is installed
npm list alpinejs
# If missing: npm install alpinejs
```

---

## 📋 Scheduled Task Issues

### Check what's scheduled

```bash
php artisan schedule:list
```

### Run a specific command manually

```bash
php artisan documents:retry-verification --hours=2
php artisan vouchers:expire
php artisan eligibility:process
php artisan audit:clean
```

---

## 🔄 Full Reset

If everything is broken and you want to start fresh:

```bash
# 1. Drop all tables and re-migrate
php artisan migrate:fresh --seed

# 2. Clear all caches
php artisan optimize:clear

# 3. Rebuild frontend
npm run build

# 4. Restart services
# Restart PostgreSQL from XAMPP
# Re-run: composer run dev (in terminal 2)
# Re-run: uvicorn... (in terminal 3)
```

---

## 📁 Log Locations

| Log | Path |
|-----|------|
| Laravel errors | `storage/logs/laravel.log` |
| Laravel AI logs | `storage/logs/laravel.log` (search for `ai.` or `AiGateway`) |
| Queue job logs | `storage/logs/laravel.log` (search for job class name) |
| PHP errors | XAMPP `php_errors.log` or `C:\xampp\php\logs\php_error.log` |
| AI Service logs | Console output (stdout) |
| Apache error log | `C:\xampp\apache\logs\error.log` |
| PostgreSQL log | `C:\xampp\postgresql\data\pg_log\` |

---

## 🧪 Testing

```bash
# Laravel tests
php artisan test

# AI service tests
cd ai_service && pytest tests/ -v
```
