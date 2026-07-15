<div align="center">
  <h1>DMRMS</h1>
  <p><strong>Digital Military Recruitment Management System</strong></p>
  <p>AI-powered recruitment platform for the Ghana Armed Forces</p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Python-3.9+-3776AB?style=flat-square&logo=python&logoColor=white" alt="Python">
    <img src="https://img.shields.io/badge/FastAPI-0.115-009688?style=flat-square&logo=fastapi&logoColor=white" alt="FastAPI">
    <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white" alt="PostgreSQL">
    <img src="https://img.shields.io/badge/OpenAI-412991?style=flat-square&logo=openai&logoColor=white" alt="OpenAI">
    <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  </p>
</div>

---

## Overview

DMRMS is a comprehensive, AI-powered web platform that digitizes the Ghana Armed Forces recruitment process — from voucher registration and application submission through automated eligibility screening, shortlisting, appointment scheduling, physical screening, and final selection.

### Key Features

- **Voucher-based Registration** — Secure serial/pin validation for applicant registration
- **Multi-step Application** — Guided form with auto-save, document upload, and review
- **Automated Eligibility Engine** — Rule-based scoring against cycle requirements
- **AI Intelligence Layer** — Document fraud detection, eligibility advising, smart shortlisting, applicant chatbot, and report generation (powered by OpenAI)
- **Screening Management** — Medical, fitness, and interview result recording
- **Appointment Scheduling** — Slot management with applicant notifications
- **Shortlisting & Final Selection** — Committee workflow with admit/defer/reject decisions
- **Admin Dashboard** — KPI cards, real-time charts, PDF/Excel reporting
- **Multi-channel Notifications** — Email, SMS, and in-app notifications
- **Full Audit Trail** — All actions logged with user, timestamp, and IP
- **RBAC** — Roles: Super Admin, Admin, Screening Officer, Medical Officer, Applicant

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.2+, Laravel 12 |
| **Frontend** | Blade, Tailwind CSS 3, Alpine.js, Chart.js |
| **Database** | PostgreSQL 16 |
| **AI Service** | Python 3.9+, FastAPI, OpenAI API |
| **Queue** | Laravel Queue (database driver) |
| **Auth** | Laravel Sanctum (API) + Session (Web) |
| **Permissions** | Spatie Laravel-permission |
| **Assets** | Vite, NPM |

### Key Packages

| Package | Purpose |
|---------|---------|
| `laravel/sanctum` | API authentication (token-based) |
| `spatie/laravel-permission` | Role-based access control |
| `barryvdh/laravel-dompdf` | PDF report generation |
| `phpoffice/phpspreadsheet` | Excel/CSV report export |
| `livewire/livewire` | Reactive UI components |
| `laravel/breeze` | Authentication scaffolding (Blade + Alpine) |

---

## Project Structure

```
dmrms/
├── app/                    # Laravel application logic
│   ├── Console/Commands/   # Artisan commands (4)
│   ├── Http/Controllers/   # API + Web controllers (10)
│   ├── Http/Middleware/     # Custom middleware (3)
│   ├── Http/Requests/      # Form request validation (10)
│   ├── Http/Resources/     # API resources (9)
│   ├── Mail/               # Mailables (7)
│   ├── Models/              # Eloquent models (16)
│   ├── Services/            # Business logic services (7+)
│   └── View/Components/     # Blade components
├── ai_service/             # Python FastAPI microservice
│   ├── app/routers/        # API endpoints (chat, vision, embeddings, analytics)
│   ├── app/services/       # OpenAI client, fallback, prompt manager
│   └── prompts/            # Prompt templates (4)
├── config/                 # Laravel configuration (15+ files)
├── database/               # Migrations (22), seeders (5), factories (6)
├── resources/views/        # Blade templates (40+)
│   ├── public/             # Landing, eligibility, announcements, FAQ
│   ├── applicant/          # Dashboard, application, documents, status
│   ├── admin/              # Dashboard, cycles, scheduling, reports
│   ├── screening/          # Verify, medical, fitness, interview
│   ├── components/         # Reusable UI components
│   └── emails/             # Email templates (7)
├── resources/js/           # Alpine.js components (4)
├── routes/                 # Web + API routes
├── tests/                  # PHPUnit tests
└── docs/                   # Documentation
```

---

## Quick Start

### Prerequisites

- PHP 8.2+, Composer, Node.js 18+, Python 3.9+
- PostgreSQL 16
- XAMPP (optional — for Apache + PHP)

### Installation

```bash
git clone <repository-url> dmrms
cd dmrms

composer install
npm install
npm run build

cp .env.example .env
# Edit .env with your database credentials

php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
```

### AI Service

```bash
cd ai_service
python -m venv venv
venv\Scripts\activate    # Windows
pip install -r requirements.txt
cp .env.example .env
# Edit ai_service/.env with your OpenAI key
uvicorn app.main:app --reload --port 8000
```

### Start All Services

```bash
# Terminal 1 — Start XAMPP (Apache & PostgreSQL via Control Panel)

# Terminal 2 — Laravel All-in-One (Queue + Scheduler + Vite + Built-in Server)
composer run dev
# Starts: php artisan serve, queue:work (--tries=3), schedule:work, npm run dev
# Access at http://localhost:8000
# One Ctrl+C kills all processes

# Terminal 3 — Python AI Service (only if AI_PROVIDER=api)
cd ai_service && venv\Scripts\activate && uvicorn app.main:app --reload --port 8000
```

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@dmrms.gov.gh` | `admin123` |
| Applicants | various | `password123` |

---

## API Endpoints

### Public
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/cycles/active` | Active recruitment cycles |
| POST | `/api/v1/eligibility/pre-check` | Pre-submission eligibility check |

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register with voucher |
| POST | `/api/v1/auth/login` | Login (returns Sanctum token) |
| POST | `/api/v1/auth/logout` | Revoke token |

### Applicant (authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/PUT | `/api/v1/applicant/profile` | Profile management |
| POST | `/api/v1/applicant/application/submit` | Submit application |
| POST | `/api/v1/applicant/documents` | Upload document |
| GET | `/api/v1/applicant/status` | Application status |

### Admin (admin/super_admin)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/dashboard/stats` | Dashboard KPIs |
| GET/POST | `/api/v1/admin/cycles` | Cycle management |
| POST | `/api/v1/admin/applications/shortlist` | Shortlist applicants |
| GET | `/api/v1/admin/reports/export` | Export reports (PDF/Excel) |

### Screening
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/screening/verify-entry` | Verify QR code entry |
| POST | `/api/v1/screening/medical` | Record medical results |
| POST | `/api/v1/screening/fitness` | Record fitness test |
| POST | `/api/v1/screening/interview` | Record interview score |

### AI (premium)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/ai/eligibility/analyze` | AI eligibility analysis |
| POST | `/api/v1/ai/documents/verify` | Document fraud detection |
| POST | `/api/v1/ai/report/generate` | Natural language report |
| POST | `/api/v1/ai/chatbot` | AI assistant chat |

Full API docs: [docs/api.md](docs/api.md)

---

## AI Service Endpoints (FastAPI — port 8000)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Service health check (no auth) |
| POST | `/chat/assist` | Applicant assistant |
| POST | `/vision/analyze` | Document analysis |
| POST | `/embeddings/generate` | Text embeddings |
| POST | `/analytics/report` | Report generation |

Swagger UI: `http://localhost:8000/docs`

---

## Documentation

- [Setup Guide](docs/setup.md) — Full installation & configuration
- [Architecture](docs/architecture.md) — System design & data flows
- [API Reference](docs/api.md) — Complete endpoint documentation
- [PRD](docs/prd.md) — Product Requirements Document (source of truth)
- [Troubleshooting](docs/troubleshooting.md) — Common issues

---

## Team

| Member | Area |
|--------|------|
| **Kweku Afredu** | AI Service, OpenAI, AI Gateway |
| **Eugene Amoah** | Laravel Backend, Services, Queues |
| **Prosper Effah** | Database, Config, Secrets, Routes |
| **Stephen Ansah** | Blade Templates, Tailwind, UI |
| **Shafatu Saddick** | Alpine.js, Chart.js, Frontend |

---

## License

Proprietary — Ghana Armed Forces / Defence Manpower Recruitment Management System
