# Fullstack AI Agent Guidance
**AI Agent Instructions | For:** Claude Code, Copilot, OpenCode  
**When to use this:** Working on features, fixes, or improvements in the DMRMS codebase  
**Key principle:** Think deeply, ask questions, break work into chunks, validate thoroughly. Add professional comments to code.

## 🎯 Project Overview
**Digital Military Recruitment Management System (DMRMS)** — An enterprise, AI-powered platform transforming the Ghana Armed Forces (GAF) recruitment process. 
- **Core:** Voucher-based registration, multi-step applications, automated eligibility, appointment scheduling, and physical screening management.
- **AI Layer:** OpenAI integration for document fraud detection, AI eligibility advising, smart shortlisting, and an applicant chatbot.
- **Tech Stack:** Laravel 11/12 (PHP 8.2+) · FastAPI (Python) · OpenAI API · PostgreSQL · Tailwind CSS · Alpine.js · XAMPP.
- **Team:** Seidu (AI Service), Ackon (Laravel Backend), Chrispen (DB/Config), Agartha (Blade/UI), Johnson (JS/Alpine).

## 📐 System Architecture
```text
┌─────────────────────────────────────────────────────────────┐
│  Presentation Layer (Blade + Tailwind + Alpine.js)          │
│  - Public Portal  - Applicant Dashboard  - Admin Panel      │
└──────────────────────────────┬──────────────────────────────┘
                               │ REST API / Web Routes
┌──────────────────────────────▼──────────────────────────────┐
│  Application Logic (Laravel 11/12)                          │
│  - Controllers, Services, Middleware, RBAC, Queues          │
│  ┌───────────────────────────────────────────────────────┐  │
│  │           AI Gateway Service Layer                    │  │
│  │  - Abstraction Layer (OpenAI / Fallback)              │  │
│  │  - Modules: Chat, Vision, Eligibility, Analytics      │  │
│  └──────────────────────┬────────────────────────────────┘  │
└─────────────────────────┼───────────────────────────────────┘
                          │ Internal HTTP (Guzzle)
┌─────────────────────────▼───────────────────────────────────┐
│  AI Orchestration (Python FastAPI, port 8000)               │
│  - Heavy AI processing, OpenAI API calls, Prompt Management │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│  Data Layer (MySQL/MariaDB, port 3306)                      │
│  - Applicants, Applications, Documents, Cycles, Audit Logs  │
└─────────────────────────────────────────────────────────────┘
```
**Design principle:** Core business logic lives in Laravel Services. Heavy AI/ML tasks are offloaded to the Python FastAPI microservice to keep the PHP application fast and responsive.

## 🤖 Before You Start: Agent Decision Tree
Use this checklist BEFORE making changes:
1. **Understand the task:** Read request, identify component, check `AGENTS.md` for ownership.
2. **Plan the work:** Break into 3-5 small chunks. Create a todo list. Save to `memory/`.
3. **Explore before coding:** Read relevant source files. Check for existing patterns (DRY).
4. **Suggest improvements:** Does it align with Laravel best practices? Is it secure?
5. **Implement incrementally:** Make one change at a time. Test after EACH change.
6. **Validate thoroughly:** Run tests. Check logs. Update memory.

## 📍 Key File Locations
| Path | Purpose | Owner |
|---|---|---|
| `ai_service/app/routers/` | FastAPI endpoints (vision, chat, analytics) | Seidu |
| `ai_service/app/services/` | OpenAI client, prompt manager, fallbacks | Seidu |
| `app/Services/Ai/` | Laravel AI Gateway & Provider abstraction | Seidu / Ackon |
| `app/Http/Controllers/Api/V1/` | Versioned REST API Controllers | Ackon |
| `app/Services/` | Core business logic (Eligibility, Voucher, etc.) | Ackon |
| `app/Models/` | Eloquent Models & Relationships | Chrispen |
| `database/migrations/` | Database schema definitions | Chrispen |
| `resources/views/` | Blade templates (layouts, dashboards, forms) | Agartha |
| `resources/css/` | Tailwind CSS configurations | Agartha |
| `resources/js/` | Alpine.js components, Chart.js scripts | Johnson |
| `.env` / `ai_service/.env` | Secrets (API keys, tokens) — contact Chrispen | Chrispen |

## 🚀 Getting Started: Project Startup (Daily)
**Prerequisites:** XAMPP (PHP 8.2+, PostgreSQL), Python 3.9+, Composer, Node/npm.

```bash
# 1. Start XAMPP (Apache & PostgreSQL via Control Panel)

# 2. Laravel All-in-One (Queue + Scheduler + Vite + Built-in Server)
cd C:\xampp\htdocs\dmrms
composer run dev
# Starts: php artisan serve, queue:work (--tries=3), schedule:work, npm run dev

# 3. Python AI Service (only if AI_PROVIDER=api in .env)
cd C:\xampp\htdocs\dmrms\ai_service
venv\Scripts\activate
uvicorn app.main:app --reload --port 8000
```
**Verify:**
- Laravel (built-in server): `http://localhost:8000`
- Laravel (via XAMPP): `http://localhost/dmrms/public`
- AI Swagger: `http://localhost:8000/docs`
- AI Health: `GET http://localhost:8000/api/v1/health`

## 💾 Memory & Caching Strategy for Agents
- **Session Memory (`memory/session/`):** Current task plans, progress, findings. (Short-term)
- **User Memory (`memory/`):** Recurring patterns, debugging tricks, best practices. (Long-term)
- **Repository Memory (`memory/repo/`):** Project-specific facts, DB schema, API contracts. (Source of truth)
*Action:* Document your memories after each chat/conversation.

## 🐛 Common Issues & Troubleshooting
**Startup Issues**
| Issue | Cause | Solution |
|---|---|---|
| `ConnectionRefused :8000` | AI Service not running | Run `uvicorn` in `ai_service/` |
| `ConnectionRefused :5432` | PostgreSQL not running | Start pgsql service |
| `401 Unauthorized` (AI) | Bearer token mismatch | Verify `AI_SERVICE_TOKEN` in both `.env` files |
| Queue jobs stuck | Queue worker not running | Run `php artisan queue:work` |
| `Class not found` | Composer autoload issue | Run `composer dump-autoload` |

**Development Issues**
| Issue | Cause | Solution |
|---|---|---|
| Git conflict | Multiple edits to same file | Check AGENTS.md team areas; coordinate with team |
| Tests fail | Code change broke test | Read test file to understand expectations; fix incrementally |
| Secrets exposed | `.env` committed to Git | Delete from Git history: `git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch .env'` |

## ✅ Testing & Validation Strategy
**Laravel (PHP)**
- Run: `php artisan test`
- Verify: API endpoints return correct status codes, RBAC middleware blocks unauthorized access, Form Requests validate correctly.

**AI Service (Python)**
- Run: `pytest tests/ -v`
- Verify: OpenAI client handles retries, Pydantic models validate payloads, Fallback logic triggers on failure.

**Cross-Component**
- [ ] No secrets in code.
- [ ] `php -l` passes on modified PHP files.
- [ ] Commit message is clear and descriptive.

## 🔄 Data Flows & Workflows
**1. Voucher Registration & Application**
- Applicant enters Serial + PIN → Laravel validates against `vouchers` table → Creates `applicant` → Sends OTP → Account activated.
- Applicant fills multi-step form → Auto-saves to `applications` (draft) → Submits → Triggers `EligibilityEngine` job.

**2. AI Document Analysis & Fraud Detection**
- Applicant uploads document → Laravel stores in `storage/app/public` → Dispatches `ProcessDocumentFraudCheck` job.
- Laravel AI Gateway sends image to Python FastAPI (`/api/v1/vision/analyze`).
- Python calls OpenAI Vision → Extracts text → Compares with application data → Returns risk score.
- Laravel updates `documents` table with `fraud_risk_score` and `ai_extracted_data`.

**3. Automated Eligibility Screening**
- Triggered on application submission.
- Laravel `EligibilityEngine` checks rules (Age, Height, Education, etc.) against `cycles` requirements.
- Updates `eligibility_results` table. If passed, generates `verification_code` and notifies applicant.

## 🔐 Security & Best Practices
- **Authentication:** Laravel Sanctum for API, standard session for web.
- **Authorization:** Strict RBAC via Middleware (`RoleMiddleware`, `SubscriptionMiddleware`).
- **AI Data Protection:** Anonymize PII before sending to OpenAI. Use zero-retention policies. Never send raw passwords or unhashed sensitive data.
- **Secrets:** Never hardcode. Use `.env`. 
- **Compliance:** Adhere to Ghana Data Protection Act. Log all admin actions in `audit_logs`.

## 🎨 Frontend Pattern (Blade + Alpine.js)
DMRMS uses Laravel Blade for rendering and Alpine.js for reactive UI components (replacing heavy SPAs).

**Example: Multi-step Form Progress Bar**
```html
<!-- Blade Template -->
<div x-data="{ step: 1, totalSteps: 4 }">
    <!-- Progress Bar -->
    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
        <div class="bg-gaf-red h-2.5 rounded-full transition-all duration-300" 
             :style="`width: ${(step / totalSteps) * 100}%`"></div>
    </div>

    <!-- Step Content -->
    <div x-show="step === 1"> ... Personal Info ... </div>
    <div x-show="step === 2"> ... Education ... </div>

    <!-- Navigation -->
    <button @click="step > 1 && step--" :disabled="step === 1">Previous</button>
    <button @click="step < totalSteps && step++" :disabled="step === totalSteps">Next</button>
</div>
```

## 📋 Git Workflow (Single `main` Branch)
```bash
git pull origin main    # Always pull first
# Make changes, test thoroughly
git add .
git commit -m "Brief description of change"
git push origin main
```
**Good commit messages:**
✅ "Add rate limiting validation to AI chatbot endpoint"
✅ "Fix duplicate voucher validation logic in registration"
❌ "Update" or "Fix bug"

## 🎯 AI Agent Quick Reference
**When you're stuck:**
1. Check memory: `memory/session/` and `memory/`
2. Read `docs/`: `prd.md` and `workflow.md ` are the ultimate source of truth, `./AGENTS.md` and `./CLAUDE.md` are AI agent files.
3. Verify endpoints: Test with Swagger (`http://localhost:8000/docs`) or Postman.
4. Run tests: `php artisan test` and `pytest tests/ -v`.
5. Ask questions: If the PRD is ambiguous, ask the user for clarification.

*Remember: Think deeply, plan before coding, test after changes, document lessons learned.*
```
