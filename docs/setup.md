# DMRMS Setup Guide

**Digital Military Recruitment Management System**

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Environment Setup](#2-environment-setup)
  - [2.1 XAMPP (Windows - Primary)](#21-xampp-windows---primary)
  - [2.2 PostgreSQL Setup](#22-postgresql-setup)
  - [2.3 Docker (Alternative)](#23-docker-alternative)
3. [Laravel Application Setup](#3-laravel-application-setup)
4. [AI Service Setup](#4-ai-service-setup)
5. [Database Seeding](#5-database-seeding)
6. [Queue Worker & Scheduler](#6-queue-worker--scheduler)
7. [Starting All Services](#7-starting-all-services)
8. [Default Credentials](#8-default-credentials)
9. [Verification](#9-verification)
10. [Troubleshooting](#10-troubleshooting)

---



## 1. Prerequisites


| Software          | Version       | Purpose                                     |
| ----------------- | ------------- | ------------------------------------------- |
| PHP               | 8.2+          | Laravel runtime                             |
| Composer          | 2.x           | PHP dependency manager                      |
| Node.js           | 18+           | Frontend asset build                        |
| NPM               | 10+           | JS dependency manager                       |
| Python            | 3.9+          | AI microservice                             |
| PostgreSQL        | 15+           | Database                                    |
| Git               | Latest        | Version control                             |
| ImageMagick       | 7.x           | PDF → image conversion (optional)           |
| GhostScript       | 10.x          | PDF rendering for ImageMagick (optional)    |
| Poppler (pdftoppm)| 23.x          | PDF → image alternative to Imagick (optional) |


**PHP Extensions Required:**

- `pdo_pgsql` — PostgreSQL driver
- `pgsql` — native PostgreSQL functions
- `gd` — image processing (passport photo validation)
- `fileinfo` — MIME type detection
- `zip` — compressed exports
- `curl` — HTTP requests to AI service
- `mbstring` — multi-byte string support
- `xml` — XML parsing
- `bcmath` — arbitrary precision math
- `imagick` — **optional** but recommended for PDF → image conversion (AI document verification)

> **XAMPP users:** Most of these are enabled by default. To verify: `php -m`

---



### 1.1 Optional: PDF-to-Image Dependencies (for AI Document Verification)

The AI service can only analyze image files (JPEG, PNG, WebP). PDF documents must be
converted to images before AI analysis. Install **one** of these options:

#### Option A: Imagick + GhostScript (Recommended — faster, in-process)

```powershell
# 1. Install ImageMagick binaries
winget install ImageMagick.ImageMagick.Q16

# 2. Download & install GhostScript
# Download from: https://ghostscript.com/releases/gsdnld.html
# Run the installer (e.g. gs10071w64.exe)

# 3. Download php_imagick.dll
# Visit: https://mlocati.github.io/articles/php-windows-imagick.html
# Find the row matching your PHP version (8.x), Thread Safe (Yes), Architecture (x64)
# Download the ZIP (e.g. php_imagick-3.8.1-8.4-ts-vs17-x64.zip)

# 4. Install the DLL
# Extract php_imagick.dll → C:\xampp\php\ext\
# Extract CORE_RL_*.dll and IM_MOD_RL_*.dll → C:\xampp\php\
# Extract config/ folder → C:\xampp\php\config\

# 5. Enable in php.ini
# Add this line to C:\xampp\php\php.ini:
# extension=php_imagick

# 6. Restart Apache and verify
php -m | findstr imagick
```



#### Option B: Poppler (Simpler — one command, external process)

```powershell
winget install oschwartz10612.Poppler
# Verify:
pdftoppm --version
```

> Without either option, PDF documents will still be accepted at upload but will skip
> AI analysis and require manual admin review.

---



## 2. Environment Setup



### 2.1 XAMPP (Windows - Primary)

XAMPP provides Apache + PHP in a single package for local development.

1. Download and install [XAMPP](https://www.apachefriends.org/) with PHP 8.2+
2. Ensure the required PHP extensions are enabled in `php.ini`
3. (Optional) Start Apache from the XAMPP Control Panel
4. The Laravel project lives inside the XAMPP `htdocs` directory:

```
C:\xampp\htdocs\dmrms\
```

Or use a symlink if the project is elsewhere:

```powershell
# Run as Administrator
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\dmrms" -Target "C:\Users\YourName\Projects\dmrms\public"
```



### 2.2 PostgreSQL Setup

The system uses PostgreSQL by default (`DB_CONNECTION=pgsql`).

1. Install [PostgreSQL 16](https://www.postgresql.org/download/)
2. During installation, set password for the `postgres` user
3. Open **pgAdmin** or use the SQL Shell (psql) to create the database:

```sql
CREATE DATABASE dmrms;
CREATE USER dmrms WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE dmrms TO dmrms;
```

1. Enable the PHP PostgreSQL extension:
  - In `php.ini`, uncomment: `extension=pdo_pgsql` and `extension=pgsql`



### 2.3 Docker (Alternative)

If you prefer containerized setup, use the included `docker-compose.yml`:

```bash
docker compose up -d
```

This starts:

- **app** — PHP 8.2 FPM + Nginx on port 80
- **db** — PostgreSQL 16 on port 5432
- **ai_service** — Python FastAPI on port 8000

---



## 3. Laravel Application Setup



### 3.1 Clone & Install Dependencies

```bash
cd C:\xampp\htdocs
git clone <repository-url> dmrms
cd dmrms

composer install
npm install
```



### 3.2 Configure Environment

```bash
copy .env.example .env
```

Edit `.env` with your database and application settings:

```ini
APP_NAME=DMRMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/dmrms/public

# PostgreSQL (default)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dmrms
DB_USERNAME=dmrms
DB_PASSWORD=your_password


# Mail (use Mailtrap for development)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@dmrms.gov.gh"
MAIL_FROM_NAME="${APP_NAME}"

# AI Service
AI_SERVICE_URL=http://localhost:8000
AI_SERVICE_TOKEN=dmrms-internal-key-2026
OPENAI_API_KEY=sk-your-key-here
```



### 3.3 Generate App Key & Link Storage

```bash
php artisan key:generate
php artisan storage:link
```



### 3.4 Run Migrations

```bash
php artisan migrate
```

This creates all 22 tables:

- `users`, `personal_access_tokens`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
- `cycles`, `vouchers`, `applicants`, `applications`, `documents`
- `eligibility_results`, `verification_codes`, `appointments`, `screening_results`, `final_decisions`
- `administrators`, `audit_logs`, `notifications`
- `ai_prompts`, `ai_predictions`, `ai_usage`
- `permissions`, `roles`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`



### 3.5 Seed the Database

```bash
php artisan db:seed
```

This populates:

- **3 administrators** (1 super_admin, 2 admin)
- **3 recruitment cycles** (one active, one upcoming, one past)
- **100 vouchers** (linked to cycles)
- **10 applicants** with submitted applications, documents, and eligibility results



### 3.6 Build Frontend Assets

```bash
npm run build
```

For development with hot-reload:

```bash
npm run dev
```

---



## 4. AI Service Setup

The AI microservice is a Python FastAPI application in the `ai_service/` directory.

### 4.1 Create Virtual Environment

```bash
cd ai_service
python -m venv venv

# Activate (Windows)
venv\Scripts\activate

# Activate (Linux/Mac)
source venv/bin/activate
```



### 4.2 Install Dependencies

```bash
pip install -r requirements.txt
```



### 4.3 Configure AI Service

```bash
copy .env.example .env
```

Edit `ai_service/.env`:

```ini
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-4-turbo
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
INTERNAL_API_KEY=dmrms-internal-key-2026
RATE_LIMIT_PER_MINUTE=10
LOG_LEVEL=INFO
```

> **Note:** The `INTERNAL_API_KEY` must match `AI_SERVICE_TOKEN` in the Laravel `.env` file.



### 4.4 Start the AI Service

```bash
uvicorn app.main:app --reload --port 8000
```

Verify: `GET http://localhost:8000/api/v1/health` returns `{"status": "healthy", ...}`

---



## 5. Database Seeding



### 5.1 Run All Seeders

```bash
php artisan db:seed
```



### 5.2 Individual Seeders

```bash
php artisan db:seed --class=CycleSeeder
php artisan db:seed --class=VoucherSeeder
php artisan db:seed --class=ApplicantSeeder
php artisan db:seed --class=AdministratorSeeder
```



### 5.3 Reset & Re-seed

```bash
php artisan migrate:fresh --seed
```

---



## 6. Queue Worker & Scheduler

The system uses Laravel's database queue driver for background jobs (emails, eligibility processing, AI tasks).

### 6.1 Start Both (Recommended)

```bash
# Runs queue:work + schedule:work + serve + Vite in one terminal
composer run dev
```



### 6.2 Start Individually

```bash
# Terminal: Process jobs continuously
php artisan queue:work --tries=3

# Terminal: Run scheduled tasks every minute
php artisan schedule:work
```

Scheduled commands:


| Command | Frequency | Description |
|---------|-----------|-------------|
| `expire:vouchers` | Daily | Marks expired vouchers |
| `cycles:auto-deactivate` | Hourly | Auto-deactivates past cycles |
| `eligibility:process-batch` | Every 5 min | Batch eligibility checks |
| `screening:send-reminders` | Daily at 8 AM | Sends screening reminders |
| `audit:clean` | Weekly (Sun) | Cleans old audit logs |
| `shortlist:auto` | Every 15 min | Auto-shortlist eligible applicants |
| `schedule:auto` | Every 15 min (6AM–8PM) | Auto-assign appointment slots |
| `decision:auto` | Daily at 2 AM | Auto-process final decisions |
| `reserve:promote` | Every 15 min (6AM–8PM) | Promote from reserve list |
| `app:purge-soft-deleted` | Daily at 3 AM | Purge soft-deleted records |
| `app:cleanup-drafts` | Daily at 4 AM | Clean abandoned draft applications |
| `documents:retry-verification` | Every 10 min | Retry AI verification for docs stuck in pending/needs_review |


---



## 7. Starting All Services



### Step 1 — XAMPP

Start Apache & PostgreSQL from the XAMPP Control Panel manually.

### Step 2 — Laravel All-in-One (Recommended)

```bash
# Starts serve + queue:work (--tries=3) + schedule:work + Vite concurrently
# One Ctrl+C kills all processes
composer run dev
```

Access at: `http://localhost:8000`

### Step 3 — Python AI Service (Optional)

Only needed if `AI_PROVIDER=api` is set in `.env`.

```bash
cd ai_service
venv\Scripts\activate
uvicorn app.main:app --reload --port 8000
```



### Alternative: Start Individually

```bash
# Laravel dev server
php artisan serve --port=8000

# Queue worker
php artisan queue:work --tries=3

# Scheduler
php artisan schedule:work

# Vite hot-reload
npm run dev
```

---



## 8. Default Credentials

After seeding:

### Administrator


| Role        | Email                 | Password   |
| ----------- | --------------------- | ---------- |
| Super Admin | `admin@dmrms.gov.gh`  | `admin123` |
| Admin       | `admin2@dmrms.gov.gh` | `admin123` |
| Admin       | `admin3@dmrms.gov.gh` | `admin123` |




### Applicants


| Email                                      | Password      |
| ------------------------------------------ | ------------- |
| See `database/seeders/ApplicantSeeder.php` | `password123` |


---



## 9. Verification

Run these checks to confirm the system is working:

### 9.1 Web Access

```
http://localhost:8000        → Landing page (200)
http://localhost:8000/login  → Login page (200)
```



### 9.2 API Health

```bash
curl http://localhost:8000/api/v1/cycles/active
# → {"data": [...], "message": "Active cycles retrieved"}
```



### 9.3 AI Service Health

```bash
curl http://localhost:8000/api/v1/health
# → {"status": "healthy", "service": "DMRMS AI Service", "version": "1.0.0"}
```



### 9.4 AI Document Verification Flow

To test the full AI document verification pipeline:

1. **Upload a document** as an applicant → it starts in `pending` status
2. **Finalize documents** → triggers `ProcessDocumentVerification` job
3. **Job fires** → file is converted to image → sent to AI Vision API → OCR extracts text → cross-referenced against applicant data → verdict returned
4. **If confident** → doc auto-verified → if all 4 required docs now verified → **application auto-advances** to `documents_verified` → eligibility auto-runs
5. **If uncertain** (`needs_review`) → job re-dispatches at +5 min, then +15 min (2 retries max)
6. **If still uncertain after retries** → left as `needs_review` for admin manual review
7. **Safety net** → `documents:retry-verification` runs every 10 min, catches any missed docs

### 9.5 Vite Build Check

```bash
npm run build
# Expected: CSS ~50kB, JS ~306kB
```



### 9.5 Run Tests

```bash
# Laravel tests
php artisan test

# AI service tests
cd ai_service && pytest tests/ -v
```

---



## 10. Troubleshooting


| Problem                         | Likely Cause                     | Solution                                        |
| ------------------------------- | -------------------------------- | ----------------------------------------------- |
| `ConnectionRefused :8000`       | AI Service not running           | Start `uvicorn` in `ai_service/`                |
| `ConnectionRefused :5432`       | PostgreSQL not running           | Start PostgreSQL service                        |
| `Target class does not exist`   | Composer autoload stale          | Run `composer dump-autoload`                    |
| `SQLSTATE[08006]`               | DB credentials wrong             | Check `.env` database settings                  |
| `No application encryption key` | `APP_KEY` missing                | Run `php artisan key:generate`                  |
| `500 on landing page`           | Route name mismatch              | Check `route()` calls in Blade files            |
| `Vite manifest not found`       | Assets not built                 | Run `npm run build`                             |
| `419 Page Expired`              | CSRF token missing               | Clear browser cache/cookies                     |
| Queue jobs not processing       | Worker not running               | Start `php artisan queue:work`                  |
| AI health check fails           | `.env` missing or token mismatch | Check `ai_service/.env` exists and tokens match |




### Log Locations

- **Laravel errors:** `storage/logs/laravel.log`
- **AI Service logs:** Console output (stdout)
- **PHP errors:** XAMPP `php_errors.log` or `error_log`



### Reset Everything

```bash
# Full reset (drops all tables, re-runs migrations, re-seeds)
php artisan migrate:fresh --seed

# Clear cached config, routes, views
php artisan optimize:clear

# Rebuild frontend
npm run build
```

