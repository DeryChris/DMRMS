# Operational Checklist & Team Coordination
**For:** AI agents, OpenCode, Copilot, human developers  
**Purpose:** Quick reference for startup, validation, and team coordination  
**Full guidance:** See [CLAUDE.md](CLAUDE.md) for comprehensive AI agent workflow

## ✅ Pre-Work Checklist
Before you start ANY task:
- [ ] Read the request completely — understand scope and requirements
- [ ] Load Agent Skills - Analyse this dir `C:\Users\amkch\ruflo-main\.agents\skills` (or your local equivalent) and pick the best agent skill(s) for the task, then load and use it in the project.
- [ ] Check team ownership (see "Team Work Areas" below) — coordinate with owner if needed
- [ ] Review `docs/` folder and `prd.md` — existing patterns, architecture, setup
- [ ] Ask clarifying questions if task is ambiguous (use `vscode_askQuestions` or equivalent)
- [ ] Plan with `manage_todo_list` — break into small chunks
- [ ] Comments: Add professional comments to the codebase for easy reference.
- [ ] Save plan to `/memories/session/` — make progress visible
- [ ] Store memory: Document your memories after each chat/conversation in `memory/session/` and/or `memory/`

## 🚀 Startup Commands (Daily)
All services required; ~2 min total:

```bash
# Terminal 1: Start XAMPP (Apache & PostgreSQL)
# XAMPP Control Panel → Start Apache and PostgreSQL (not MySQL)
# Verify: http://localhost/dmrms/public

# Terminal 2: Laravel All-in-One (Queue + Scheduler + Vite + Built-in Server)
cd C:\xampp\htdocs\dmrms
composer run dev
# Starts: php artisan serve, queue:work, schedule:work, npm run dev
# Access via: http://localhost:8000
# One Ctrl+C kills all processes

# Terminal 3: Python AI Service (FastAPI — optional, only if AI_PROVIDER=api)
cd C:\xampp\htdocs\dmrms\ai_service
venv\Scripts\activate
uvicorn app.main:app --reload --port 8000
# Verify: GET http://localhost:8000/api/v1/health → {"status": "healthy"}
```

**Key endpoints (test these):**
- Laravel App (built-in server): `http://localhost:8000`
- Laravel App (via XAMPP): `http://localhost/dmrms/public`
- AI Swagger UI: `http://localhost:8000/docs`
- AI Health (no auth): `GET http://localhost:8000/api/v1/health`

## 🧪 Testing & Validation
**Laravel Backend (PHP)**
```bash
cd C:\xampp\htdocs\dmrms
php artisan test            # Laravel native test runner
./vendor/bin/pest           # If using Pest
```

**AI Service (Python)**
```bash
cd C:\xampp\htdocs\dmrms\ai_service
venv\Scripts\activate
pytest tests/ -v
```

**Before Committing**
- [ ] `php -l` shows no syntax issues on modified PHP files
- [ ] No secrets in code (check for `.env`, API keys, tokens)
- [ ] Tests pass
- [ ] All changes follow team conventions

## 👥 Team Work Areas (Avoid Conflicts)
| Owner | Components | Key Files |
|---|---|---|
| **Seidu** | AI Service architecture, OpenAI integration, AI Gateway | `ai_service/`, `app/Services/Ai/` |
| **Ackon** | Laravel backend, controllers, services, eligibility engine, queues | `app/Http/Controllers/`, `app/Services/`, `app/Jobs/` |
| **Chrispen** | Database, migrations, config, secrets, XAMPP setup | `database/`, `config/`, `.env`, `routes/` |
| **Agartha** | Blade templates, Tailwind CSS, UI layouts, components | `resources/views/`, `resources/css/` |
| **Johnson** | Alpine.js, Chart.js, frontend interactions, form validation | `resources/js/`, `public/assets/` |

*Coordination: If your task touches another team member's area, coordinate first (chat or comment in code).*

## 🔐 Secrets Management
**Golden rule:** Never commit `.env`, API keys, or tokens.
- **Holder:** Chrispen holds all secrets
- **Locations:** 
  - Laravel: `.env` (App key, DB credentials, Mail)
  - Python: `ai_service/.env` (OpenAI keys, Internal API token)
- **If exposed:** Contact Chrispen immediately and rotate keys.
- **Secrets to guard:**
  - `OPENAI_API_KEY` — OpenAI API key
  - `AI_SERVICE_TOKEN` — Bearer token for Laravel <-> Python internal auth
  - `DB_PASSWORD` — MySQL/MariaDB credentials
  - `APP_KEY` — Laravel application key

## 🐛 Quick Troubleshooting
| Issue | Quick Fix |
|---|---|
| `ConnectionRefused :8000` | Is AI Service running? `cd ai_service` & `uvicorn app.main:app...` |
| `ConnectionRefused :5432` | Is PostgreSQL running? Check XAMPP Control Panel. |
| Health check fails | Check `ai_service/.env` exists + Bearer token is set. |
| Laravel 500 Error | Check `storage/logs/laravel.log` for stack trace. |
| Queue jobs not processing | Is `php artisan queue:work` running in Terminal 2? |
| Git conflict | Check team areas (above); coordinate with owner; resolve manually. |
| Secrets exposed in Git | Run: `git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch .env'` |
| Emails not sending | Check `MAIL_MAILER` in `.env`. Default is `log` (writes to `storage/logs/laravel.log`). Set to `smtp` + configure SMTP credentials for real delivery. |

*For detailed troubleshooting: See [docs/troubleshooting.md](docs/troubleshooting.md)*

## 🔄 Git Workflow (Main Branch Only)
No PRs. Single `main` branch.
```bash
git pull origin main    # Always pull first
# Make your changes
php artisan test        # Test Laravel before committing
pytest tests/ -v        # Test Python before committing
git add .
git commit -m "Clear description of what changed"
git push origin main
```
**Good commit messages:**
✅ "Add rate limiting validation to AI chatbot endpoint"
✅ "Fix duplicate voucher validation logic in registration"
❌ "Update" or "Fix bug"

## 📊 Key Endpoints & Services
**AI Service (FastAPI)**
| Endpoint | Purpose | Auth |
|---|---|---|
| `GET /api/v1/health` | Service health check | None |
| `POST /api/v1/vision/analyze` | Document analysis & fraud detection | Bearer |
| `POST /api/v1/chat/assist` | Applicant Assistant (Chatbot) | Bearer |
| `POST /api/v1/eligibility/advise` | AI Eligibility Advisor | Bearer |
| `POST /api/v1/analytics/report` | Natural language report generation | Bearer |

*Swagger UI: `http://localhost:8000/docs` (interactive testing)*

**Laravel API (Core)**
- Base URL: `/api/v1/`
- Auth: Laravel Sanctum (Bearer Token)
- Key Routes: `/auth/register`, `/applicant/application`, `/admin/dashboard/stats`

**Databases**
- `dmrms` (PostgreSQL via XAMPP): Core application, applicants, applications, AI logs.
- Port: 5432

## 📚 Resources
| Resource | Purpose |
|---|---|
| [CLAUDE.md](CLAUDE.md) | Full AI agent guidance — comprehensive workflow, memory strategy |
| [prd.md](prd.md) | Complete Product Requirements Document (Source of Truth) |
| `docs/architecture.md` | System design, data flows, component interactions |
| `docs/api.md` | API reference, request/response examples |
| `docs/setup.md` | Detailed installation & XAMPP environment setup |
| `docs/troubleshooting.md` | Common issues and solutions |
| http://localhost:8000/docs | Live Swagger UI for AI API testing |

## 💡 Quick Reference
**When blocked:**
- Check `/memories/session/` — current task plan
- Search `docs/` and `prd.md` — might have the answer
- Run tests — catch regressions early
- Ask questions — unclear requirements? Use `vscode_askQuestions`

**When merging changes:**
- Test thoroughly before commit
- Write clear commit message
- Push to `main` (no separate PR process)
- Verify health endpoint: `GET http://localhost:8000/api/v1/health`

**When adding features:**
- Check team areas — avoid conflicts
- Read existing tests — understand patterns
- Break work into small chunks (3-5 todos)
- Test each chunk independently
- Document in code and memory
```
