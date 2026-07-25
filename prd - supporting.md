# DMRMS — Digital Military Recruitment Management System
## Complete Product Requirements Support Document (PRD)
### For AI Agent Full-Stack Implementation

---

> **Document Version:** 1.0  
> **Prepared For:** AI Agent Build Execution  
> **Stack:** HTML5 · CSS3 · Vanilla JS · PHP 8.x · PostgreSQL 16 · XAMPP (Windows) · Python 3.x (microservice)  
> **Reference Institution:** University of Mines and Technology (UMaT), Tarkwa  
> **Inspiration:** Ghana Armed Forces official portals (gafonline.mil.gh, apply.mil.gh)  
> **Environment:** Local development on Windows via XAMPP; architecture prepared for cloud deployment

---

## TABLE OF CONTENTS

1. [Project Vision & Goals](#1-project-vision--goals)
2. [Tech Stack Decision & Architecture](#2-tech-stack-decision--architecture)
3. [Folder Structure](#3-folder-structure)
4. [Database Schema — PostgreSQL](#4-database-schema--postgresql)
5. [Visual Design System](#5-visual-design-system)
6. [Public-Facing Pages](#6-public-facing-pages)
7. [Applicant Portal](#7-applicant-portal)
8. [Recruitment Admin Portal](#8-recruitment-admin-portal)
9. [Super Admin Portal](#9-super-admin-portal)
10. [Backend PHP API Layer](#10-backend-php-api-layer)
11. [Eligibility Decision Engine](#11-eligibility-decision-engine)
12. [Notification System](#12-notification-system)
13. [Python Microservice](#13-python-microservice)
14. [Security Implementation](#14-security-implementation)
15. [Supporting Modules & Libraries](#15-supporting-modules--libraries)
16. [AI Agent Skill Instructions](#16-ai-agent-skill-instructions)
17. [Environment Setup Guide](#17-environment-setup-guide)
18. [Testing Checklist](#18-testing-checklist)
19. [Deployment Migration Path](#19-deployment-migration-path)

---

## 1. PROJECT VISION & GOALS

### 1.1 What This System Is

DMRMS is a **fully digital, web-based military recruitment management platform** for the Ghana Armed Forces context, built as a university final-year project at UMaT. It replaces the paper-based, fraud-prone, overcrowded manual recruitment workflow with a structured, transparent, and automated pipeline — from cycle creation to final admission decision.

### 1.2 Core Problem Being Solved

| Problem | Impact |
|---|---|
| Manual document handling | Slow processing, data loss |
| Certificate forgery | Unqualified candidates admitted |
| No real-time tracking | Applicants crowd physical centres |
| Inconsistent eligibility checks | Unfair, error-prone filtering |
| No analytics | Administrators fly blind |
| Overcrowding at screening venues | Security and logistical risk |

### 1.3 System Goals

- **100% digital pipeline** from application to admission decision
- **Automated eligibility engine** (6-point sequential check)
- **Unique verification code** generation for each shortlisted applicant
- **Real-time SMS + Email notifications** at every pipeline stage
- **Role-based access** for three distinct user classes
- **Analytics dashboard** with KPIs tracked per recruitment cycle
- **Production-ready code quality** — not a demo, a deployable system

### 1.4 User Roles

| Role | Access Scope |
|---|---|
| **Applicant** | Register, apply, upload docs, track status, receive codes |
| **Recruitment Admin** | Manage candidates, shortlist, schedule, generate reports |
| **Super Admin** | Full system config, user management, audit logs, backups |

---

## 2. TECH STACK DECISION & ARCHITECTURE

### 2.1 Primary Stack

| Layer | Technology | Rationale |
|---|---|---|
| **Frontend** | HTML5 + CSS3 + Vanilla JS | No build tools; works directly on XAMPP |
| **Animations** | GSAP 3 + ScrollTrigger (CDN) | Industry-standard, free for non-commercial |
| **UI Reactivity** | Alpine.js (CDN) | Lightweight Vue-like reactivity, no build step |
| **Charts** | Chart.js (CDN) | Free, well-documented, beautiful dashboards |
| **Sliders** | Swiper.js (CDN) | Touch-friendly, modern carousels |
| **Icons** | Lucide Icons + Font Awesome (CDN) | Clean, consistent icon sets |
| **Backend** | PHP 8.2 | Mature, runs on XAMPP, excellent PDO support |
| **Database** | PostgreSQL 16 | 3NF normalisation, advanced features |
| **DB Admin** | pgAdmin 4 | Replaces phpMyAdmin for PostgreSQL |
| **PDF Generation** | mPDF (PHP library via Composer) | Server-side PDF for letters, reports |
| **QR Codes** | endroid/qr-code (PHP via Composer) | Verification code QR badges |
| **Email** | PHPMailer (via Composer) | Reliable SMTP, works with Gmail/Zoho |
| **Dev Email** | MailHog (local binary) | Catch-all email testing, no real sends |
| **SMS** | Arkesel API (Ghana-based) | Pay-as-you-go, PHP-friendly, local MNOs |
| **Python** | Flask 3.x (microservice) | Eligibility scoring + PDF reports + QR |
| **Local Server** | XAMPP (Apache + PHP) | Windows-native, easy setup |

### 2.2 Why NOT Node.js / React

Node.js and React introduce a build system (Webpack/Vite), package managers (npm), JSX compilation, and a separate server process. For a Windows XAMPP environment, this creates unnecessary complexity. The vanilla JS + Alpine.js + GSAP combo achieves everything React would for this project without the overhead.

**The only place Node.js could help:** A WebSocket server for real-time dashboard updates. However, PHP long-polling or server-sent events (SSE) achieves the same result. If the builder wants real-time, use SSE — no Node.js needed.

### 2.3 Three-Tier Architecture

```
┌─────────────────────────────────────────────────────┐
│              PRESENTATION LAYER                      │
│  HTML5 / CSS3 / Vanilla JS / GSAP / Alpine.js        │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐  │
│  │ Public Pages │ │Applicant     │ │ Admin        │  │
│  │ (Landing,   │ │Portal        │ │ Dashboard    │  │
│  │  Apply Info)│ │(Dashboard,   │ │ (Reports,    │  │
│  │             │ │ Forms, Docs) │ │  Scheduling) │  │
│  └──────────────┘ └──────────────┘ └──────────────┘  │
└─────────────────────────────────────────────────────┘
                        │ HTTP Requests (AJAX/Fetch API)
                        ▼
┌─────────────────────────────────────────────────────┐
│              APPLICATION LOGIC LAYER                 │
│  PHP 8.2 (Apache/XAMPP) — RESTful API Endpoints      │
│  ┌─────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐  │
│  │  Auth   │ │Application│ │Eligibility│ │Notif.   │  │
│  │  Module │ │ Module   │ │ Engine   │ │ Module  │  │
│  │  (JWT-  │ │(CRUD,    │ │(Decision │ │(Email + │  │
│  │  like   │ │ Uploads) │ │ Rules)   │ │ SMS)    │  │
│  │sessions)│ └──────────┘ └──────────┘ └─────────┘  │
│  └─────────┘                                         │
│  ┌──────────────────────────────────────────────┐   │
│  │ Python Flask Microservice (port 5001)         │   │
│  │ — Advanced scoring, QR gen, PDF reports       │   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
                        │ PDO / pg_connect
                        ▼
┌─────────────────────────────────────────────────────┐
│                  DATA LAYER                          │
│  PostgreSQL 16 (port 5432)                           │
│  ┌─────────────────────────────────────────────┐    │
│  │ dmrms_db — 12 normalised tables (3NF)        │    │
│  └─────────────────────────────────────────────┘    │
│  /uploads/ — Document file storage (structured)     │
└─────────────────────────────────────────────────────┘
```

### 2.4 PHP Session Authentication Strategy

Since JWT requires a Node.js or special PHP-JWT library, use **PHP sessions + secure tokens** that mimic JWT behaviour:

- On login: generate a signed session token using `hash_hmac('sha256', payload, SECRET_KEY)`
- Store in `$_SESSION` server-side and send a cookie
- Middleware validates token + role on every protected page/API call
- Sessions expire after 30 minutes of inactivity
- Refresh on each valid request

This achieves stateless-style security using PHP's native session system.

---

## 3. FOLDER STRUCTURE

```
dmrms/                                  # 📁 Project Root (XAMPP DocumentRoot → /public)
│
├── app/                                # 🐘 Laravel Core Application
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── ProcessAIBatch.php
│   │   │   ├── ExpireVouchers.php
│   │   │   ├── GenerateReports.php
│   │   │   └── CleanupTempUploads.php
│   │   └── Kernel.php                  # Task scheduling (cron)
│   │
│   ├── Exceptions/
│   │   └── Handler.php                 # Global exception + API error formatting
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/                 # Versioned REST API Controllers
│   │   │   │   ├── AuthController.php          # Login, logout, token refresh
│   │   │   │   ├── VoucherController.php       # Validate voucher codes
│   │   │   │   ├── ApplicantController.php     # Applicant dashboard, profile
│   │   │   │   ├── ApplicationController.php   # Submit, save draft, view status
│   │   │   │   ├── DocumentController.php      # Upload, validate, retrieve docs
│   │   │   │   ├── EligibilityController.php   # Trigger screening, view results
│   │   │   │   ├── SchedulingController.php    # Appointment management
│   │   │   │   ├── AdminController.php         # Admin dashboard, candidate mgmt
│   │   │   │   ├── SuperAdminController.php    # System settings, user mgmt
│   │   │   │   ├── ReportController.php        # Analytics, export PDF/CSV
│   │   │   │   ├── NotificationController.php  # Send/retry notifications
│   │   │   │   └── AiController.php            # Premium AI feature endpoints
│   │   │   │
│   │   │   └── Web/                    # Blade-rendering controllers
│   │   │       ├── PublicController.php        # Landing, FAQ, announcements
│   │   │       ├── ApplicantPortalController.php
│   │   │       ├── AdminPortalController.php
│   │   │       └── SuperAdminPortalController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php              # RBAC enforcement
│   │   │   ├── SubscriptionMiddleware.php      # Pro/Enterprise AI tier check
│   │   │   ├── AiRateLimitMiddleware.php       # AI cost control & throttling
│   │   │   ├── VoucherMiddleware.php           # Voucher validation on registration
│   │   │   └── AuditLogMiddleware.php          # Logs all admin actions
│   │   │
│   │   ├── Requests/                   # Form validation classes
│   │   │   ├── VoucherValidationRequest.php
│   │   │   ├── ApplicantRegistrationRequest.php
│   │   │   ├── ApplicationSubmitRequest.php
│   │   │   ├── DocumentUploadRequest.php
│   │   │   ├── SchedulingRequest.php
│   │   │   └── AdminSettingsRequest.php
│   │   │
│   │   └── Resources/                  # API JSON response transformers
│   │       ├── ApplicantResource.php
│   │       ├── ApplicationResource.php
│   │       ├── EligibilityResultResource.php
│   │       └── ReportResource.php
│   │
│   ├── Models/                         # Eloquent Models
│   │   ├── RecruitmentCycle.php
│   │   ├── Voucher.php
│   │   ├── Applicant.php
│   │   ├── Application.php
│   │   ├── Document.php
│   │   ├── EligibilityResult.php
│   │   ├── VerificationCode.php
│   │   ├── Appointment.php
│   │   ├── ScreeningResult.php
│   │   ├── Administrator.php
│   │   ├── Notification.php
│   │   ├── AuditLog.php
│   │   └── AiUsageLog.php
│   │
│   ├── Services/                       # Core Business Logic
│   │   ├── Ai/
│   │   │   ├── AiGateway.php                   # Abstraction layer
│   │   │   ├── Providers/
│   │   │   │   ├── OpenAiProvider.php          # OpenAI API wrapper
│   │   │   │   └── FallbackProvider.php        # Rule-based / mock fallback
│   │   │   └── Modules/
│   │   │       ├── ChatbotModule.php           # Applicant Assistant
│   │   │       ├── FraudDetectionModule.php    # Document Vision analysis
│   │   │       ├── EligibilityAdvisorModule.php # AI eligibility advising
│   │   │       ├── ShortlistingModule.php      # Smart candidate ranking
│   │   │       └── AnalyticsModule.php         # Report generation
│   │   │
│   │   ├── Eligibility/
│   │   │   └── EligibilityEngine.php           # Rule-based automated screening
│   │   │
│   │   ├── Voucher/
│   │   │   └── VoucherService.php              # Generation, validation, expiry
│   │   │
│   │   ├── Notification/
│   │   │   ├── NotificationService.php         # Multi-channel dispatch orchestrator
│   │   │   ├── EmailService.php                # PHPMailer / Laravel Mail wrapper
│   │   │   └── SmsService.php                  # SMS API wrapper + local fallback
│   │   │
│   │   ├── Document/
│   │   │   ├── FileUploadService.php           # Secure file handling, mime validation
│   │   │   └── DocumentValidationService.php   # Completeness + format checks
│   │   │
│   │   ├── Export/
│   │   │   ├── PdfService.php                  # PDF generation (admission letters, reports)
│   │   │   ├── QrCodeService.php               # QR generation for verification codes
│   │   │   └── CsvExportService.php            # CSV/Excel report exports
│   │   │
│   │   ├── Scheduling/
│   │   │   └── AppointmentService.php          # Slot allocation, conflict detection
│   │   │
│   │   └── Audit/
│   │       └── AuditService.php                # Logs all admin actions with IP + timestamp
│   │
│   ├── Jobs/                           # Queue Jobs (async processing)
│   │   ├── ProcessDocumentFraudCheck.php
│   │   ├── RunEligibilityScreening.php
│   │   ├── SendBulkNotification.php
│   │   ├── GenerateVerificationCode.php
│   │   └── ProcessAiShortlisting.php
│   │
│   ├── Events/
│   │   ├── ApplicationSubmitted.php
│   │   ├── ApplicantShortlisted.php
│   │   ├── ScreeningCompleted.php
│   │   └── FinalDecisionMade.php
│   │
│   ├── Listeners/
│   │   ├── TriggerEligibilityCheck.php
│   │   ├── GenerateShortlistNotification.php
│   │   └── LogAuditTrail.php
│   │
│   ├── Mail/
│   │   ├── WelcomeMail.php
│   │   ├── EligibilityResultMail.php
│   │   ├── AppointmentConfirmationMail.php
│   │   └── FinalDecisionMail.php
│   │
│   ├── Notifications/                  # Laravel Notification classes
│   │   ├── VoucherPurchasedNotification.php
│   │   ├── ApplicationStatusNotification.php
│   │   └── AppointmentReminderNotification.php
│   │
│   ├── Policies/                       # Model authorization
│   │   ├── ApplicationPolicy.php
│   │   └── AdministratorPolicy.php
│   │
│   ├── Rules/                          # Custom validation rules
│   │   ├── ValidGhanaCard.php
│   │   ├── ValidHeight.php
│   │   ├── ValidAgeRange.php
│   │   └── UnusedVoucher.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── AuthServiceProvider.php
│       └── EventServiceProvider.php
│
├── ai_service/                         # 🐍 Python FastAPI Microservice
│   ├── app/
│   │   ├── main.py                     # FastAPI entry point
│   │   ├── routers/
│   │   │   ├── chat.py                 # Applicant Assistant (Assistants API)
│   │   │   ├── vision.py               # Document fraud detection (Vision API)
│   │   │   ├── eligibility.py          # AI eligibility advising
│   │   │   ├── shortlisting.py         # Candidate ranking & embeddings
│   │   │   └── analytics.py            # Natural language report generation
│   │   ├── services/
│   │   │   ├── openai_client.py        # OpenAI API wrapper + retry + cost tracking
│   │   │   ├── prompt_manager.py       # Loads/manages prompt templates
│   │   │   └── fallback.py             # Local OCR (Tesseract) + rule-based fallbacks
│   │   ├── models/                     # Pydantic request/response schemas
│   │   │   ├── chat.py
│   │   │   ├── vision.py
│   │   │   └── analytics.py
│   │   ├── utils/
│   │   │   ├── image_preprocessor.py   # Resize, normalize uploads
│   │   │   └── pdf_extractor.py        # Text extraction from PDFs
│   │   └── core/
│   │       ├── config.py               # Env vars, rate limits
│   │       └── security.py             # Bearer token validation (Laravel ↔ Python)
│   ├── prompts/                        # System prompt templates (JSON/TXT)
│   │   ├── chatbot_system.txt
│   │   ├── fraud_detection.txt
│   │   ├── eligibility_advisor.txt
│   │   └── report_generator.txt
│   ├── tests/
│   │   ├── test_chat.py
│   │   ├── test_vision.py
│   │   └── test_eligibility.py
│   ├── requirements.txt
│   └── .env.example
│
├── bootstrap/                          # Laravel bootstrap
│
├── config/                             # Laravel Configuration
│   ├── ai.php                          # OpenAI keys, models, fallback, budget limits
│   ├── subscription.php                # Plan limits, feature toggles
│   ├── recruitment.php                 # GAF rules (age limits, height thresholds)
│   ├── export.php                      # PDF/CSV export settings
│   ├── sms.php                         # SMS provider config (Arkesel, fallback)
│   ├── logging.php                     # Custom log channels (sms, email, security, audit)
│   └── filesystems.php                 # Disk configs (uploads, exports, temp)
│
├── database/
│   ├── migrations/                     # Schema definitions
│   │   ├── 0001_create_recruitment_cycles_table.php
│   │   ├── 0002_create_vouchers_table.php
│   │   ├── 0003_create_applicants_table.php
│   │   ├── 0004_create_applications_table.php
│   │   ├── 0005_create_documents_table.php
│   │   ├── 0006_create_eligibility_results_table.php
│   │   ├── 0007_create_verification_codes_table.php
│   │   ├── 0008_create_appointments_table.php
│   │   ├── 0009_create_screening_results_table.php
│   │   ├── 0010_create_administrators_table.php
│   │   ├── 0011_create_notifications_table.php
│   │   ├── 0012_create_audit_logs_table.php
│   │   └── 0013_create_ai_usage_logs_table.php
│   ├── seeders/
│   │   ├── AdminSeeder.php
│   │   ├── RecruitmentCycleSeeder.php
│   │   └── VoucherSeeder.php
│   └── factories/
│       ├── ApplicantFactory.php
│       └── ApplicationFactory.php
│
├── public/                             # 🌐 XAMPP DocumentRoot
│   ├── index.php                       # Laravel front controller
│   ├── .htaccess                       # Apache rewrite + security headers
│   └── assets/                         # Compiled/static frontend assets
│       ├── css/
│       │   ├── app.css                 # Compiled Tailwind + global styles
│       │   ├── landing.css             # Landing page specific
│       │   └── animations.css          # Keyframes + transitions
│       ├── js/
│       │   ├── app.js                  # Global Alpine.js init
│       │   ├── form-wizard.js          # Multi-step form logic
│       │   ├── file-upload.js          # Drag-drop upload handler
│       │   ├── dashboard.js            # Chart.js initialization
│       │   ├── status-tracker.js       # Application timeline animation
│       │   └── admin-table.js          # Filterable candidate table
│       └── images/
│           ├── logo/                   # GAF/DMRMS logos (SVG + PNG)
│           ├── hero/                   # Landing hero images
│           └── icons/                  # Custom SVG icons
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php           # Master layout (head, scripts)
│   │   │   ├── public.blade.php        # Public pages (nav + footer)
│   │   │   ├── applicant.blade.php     # Applicant portal (sidebar + nav)
│   │   │   ├── admin.blade.php         # Admin portal (sidebar + nav)
│   │   │   ├── superadmin.blade.php    # Super admin portal
│   │   │   └── screening.blade.php     # Screening officer portal
│   │   │
│   │   ├── components/                 # Reusable Blade components
│   │   │   ├── kpi-card.blade.php
│   │   │   ├── status-badge.blade.php
│   │   │   ├── progress-bar.blade.php
│   │   │   ├── file-uploader.blade.php
│   │   │   └── data-table.blade.php
│   │   │
│   │   ├── public/
│   │   │   ├── landing.blade.php       # Hero landing page
│   │   │   ├── about.blade.php
│   │   │   ├── eligibility-info.blade.php
│   │   │   ├── voucher-info.blade.php  # Voucher purchase info/outlets
│   │   │   ├── faq.blade.php
│   │   │   ├── contact.blade.php
│   │   │   ├── announcements.blade.php
│   │   │   └── login.blade.php         # Unified login page
│   │   │
│   │   ├── applicant/
│   │   │   ├── register.blade.php      # Multi-step registration
│   │   │   ├── voucher.blade.php       # Voucher validation step
│   │   │   ├── dashboard.blade.php     # Applicant home
│   │   │   ├── application-form.blade.php  # Multi-section form
│   │   │   ├── document-upload.blade.php   # Drag-and-drop uploader
│   │   │   ├── status-timeline.blade.php   # Application progress tracker
│   │   │   ├── verification-code.blade.php # View/print/download QR code
│   │   │   ├── appointment.blade.php   # Appointment details
│   │   │   └── profile.blade.php       # Edit profile info
│   │   │
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php     # Metrics + KPI cards + charts
│   │   │   ├── candidates.blade.php    # Candidate table with filters
│   │   │   ├── candidate-detail.blade.php  # Single candidate + actions
│   │   │   ├── documents-review.blade.php  # Document verification interface
│   │   │   ├── shortlisting.blade.php  # Bulk shortlist management
│   │   │   ├── scheduling.blade.php    # Appointment slot management
│   │   │   ├── notifications.blade.php # Notification log + retry
│   │   │   ├── reports.blade.php       # Analytics dashboard + exports
│   │   │   └── cycle-management.blade.php  # Manage recruitment cycles
│   │   │
│   │   ├── superadmin/
│   │   │   ├── dashboard.blade.php     # System health overview
│   │   │   ├── users.blade.php         # Admin user management
│   │   │   ├── system-settings.blade.php   # Global config (thresholds)
│   │   │   ├── audit-logs.blade.php    # Full audit trail
│   │   │   ├── backup.blade.php        # DB backup management
│   │   │   └── security.blade.php      # Roles, access, 2FA settings
│   │   │
│   │   └── screening/
│   │       ├── dashboard.blade.php     # Screening officer home
│   │       ├── verify-code.blade.php   # Scan/enter verification code
│   │       ├── medical.blade.php       # Medical exam form
│   │       ├── fitness.blade.php       # Fitness assessment form
│   │       └── interview.blade.php     # Interview scoring form
│   │
│   ├── css/
│   │   └── app.css                     # Tailwind source (builds to public/assets/css/)
│   └── js/
│       └── app.js                      # Alpine.js entry (builds to public/assets/js/)
│
├── routes/
│   ├── web.php                         # Web routes (Blade rendering)
│   ├── channels.php                    # Broadcasting
│   └── api/                            # Versioned API route files
│       └── v1/
│           ├── auth.php                # POST /api/v1/auth/login, /register, /refresh
│           ├── voucher.php             # POST /api/v1/voucher/validate
│           ├── applicant.php           # GET/POST /api/v1/applicant/*
│           ├── application.php         # GET/POST /api/v1/application/*
│           ├── document.php            # POST /api/v1/document/upload
│           ├── eligibility.php         # POST /api/v1/eligibility/run
│           ├── schedule.php            # GET/POST /api/v1/schedule/*
│           ├── admin.php               # GET/POST /api/v1/admin/*
│           ├── superadmin.php          # GET/POST /api/v1/superadmin/*
│           ├── report.php              # GET /api/v1/report/*
│           ├── notification.php        # POST /api/v1/notification/*
│           └── ai.php                  # POST /api/v1/ai/chat, /vision, /analytics
│
├── storage/
│   ├── app/
│   │   ├── uploads/                    # Applicant documents (NOT web-accessible)
│   │   │   ├── birth_certificates/
│   │   │   ├── educational_certs/
│   │   │   ├── national_ids/
│   │   │   ├── passport_photos/
│   │   │   └── temp/                   # Before validation
│   │   ├── exports/                    # Generated PDFs and reports
│   │   │   ├── admission_letters/
│   │   │   ├── reports/
│   │   │   └── verification_codes/
│   │   └── public/                     # Symlinked via `php artisan storage:link`
│   ├── framework/                      # Laravel cache, sessions, compiled views
│   └── logs/                           # Categorized application logs
│       ├── laravel.log                 # Default app log
│       ├── sms.log                     # SMS delivery log
│       ├── email.log                   # Email delivery log
│       ├── security.log                # Auth events, failed logins
│       └── audit.log                   # Admin action audit trail
|
├── docs/
│   ├── architecture.md
│   ├── api.md
│   ├── setup.md
│   ├── troubleshooting.md
│   └── workflows.md
│
├── memory/
│   ├── session/
│   ├── repo/
│   └── lessons.md
│ 
├── tests/
│   ├── Unit/
│   │   ├── EligibilityEngineTest.php
│   │   ├── VoucherServiceTest.php
│   │   └── AiGatewayTest.php
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── ApplicationSubmissionTest.php
│   │   ├── DocumentUploadTest.php
│   │   └── AdminDashboardTest.php
│   └── Browser/                        # Laravel Dusk
│       └── RegistrationFlowTest.php
│
├── .env.example                        # Master environment variables
├── .gitignore
├── artisan                             # Laravel CLI
├── composer.json
├── package.json                        # Node deps (Tailwind, Alpine.js, Chart.js)
├── vite.config.js                      # Asset bundler
├── tailwind.config.js
├── AGENTS.md
├── CLAUDE.md
├── prd.md
├── prd - Supporting.md
└── README.md

```

---

## 4. DATABASE SCHEMA — PostgreSQL

### 4.1 Setup Instructions

```sql
-- Run in pgAdmin or psql:
CREATE DATABASE dmrms_db
    WITH ENCODING = 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8';

\c dmrms_db;
CREATE EXTENSION IF NOT EXISTS "pgcrypto"; -- for gen_random_uuid()
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- for fuzzy text search
```

### 4.2 Full Schema — All Tables

```sql
-- ============================================================
-- TABLE: recruitment_cycles
-- ============================================================
CREATE TABLE recruitment_cycles (
    cycle_id        UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cycle_name      VARCHAR(100) NOT NULL,
    description     TEXT,
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    application_open_date  DATE NOT NULL,
    application_close_date DATE NOT NULL,
    total_vacancies INT NOT NULL DEFAULT 0,
    army_vacancies  INT DEFAULT 0,
    navy_vacancies  INT DEFAULT 0,
    airforce_vacancies INT DEFAULT 0,
    min_age         INT NOT NULL DEFAULT 18,
    max_age         INT NOT NULL DEFAULT 26,
    min_height_male DECIMAL(4,2) NOT NULL DEFAULT 1.65,
    min_height_female DECIMAL(4,2) NOT NULL DEFAULT 1.58,
    required_edu_level VARCHAR(50) NOT NULL DEFAULT 'WASSCE',
    status          VARCHAR(20) NOT NULL DEFAULT 'draft'
                    CHECK (status IN ('draft','active','closed','archived')),
    created_by      UUID,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: vouchers
-- ============================================================
CREATE TABLE vouchers (
    voucher_id      UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    serial_number   VARCHAR(20) UNIQUE NOT NULL,
    pin_code        VARCHAR(20) NOT NULL,  -- hashed bcrypt
    price_ghs       DECIMAL(10,2) NOT NULL DEFAULT 350.00,
    batch_ref       VARCHAR(50),
    is_used         BOOLEAN DEFAULT FALSE,
    used_at         TIMESTAMPTZ,
    purchased_by_name VARCHAR(200),
    outlet_code     VARCHAR(50),   -- Ghana Post outlet code
    expires_at      TIMESTAMPTZ NOT NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: applicants
-- ============================================================
CREATE TABLE applicants (
    applicant_id    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    voucher_id      UUID UNIQUE REFERENCES vouchers(voucher_id),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    -- Authentication
    email           VARCHAR(255) UNIQUE NOT NULL,
    phone           VARCHAR(20) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,  -- bcrypt cost 12
    -- Personal Info
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    other_names     VARCHAR(100),
    date_of_birth   DATE NOT NULL,
    gender          VARCHAR(10) NOT NULL CHECK (gender IN ('male','female')),
    nationality     VARCHAR(100) NOT NULL DEFAULT 'Ghanaian',
    is_ghanaian_citizen BOOLEAN NOT NULL DEFAULT TRUE,
    national_id_number VARCHAR(20) UNIQUE,
    ghana_card_number VARCHAR(20),
    -- Contact
    residential_address TEXT NOT NULL,
    region          VARCHAR(100) NOT NULL,
    district        VARCHAR(100),
    gps_address     VARCHAR(50),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    -- Service Preference
    preferred_service VARCHAR(20) CHECK (preferred_service IN ('army','navy','airforce','any')),
    -- Account Status
    email_verified  BOOLEAN DEFAULT FALSE,
    phone_verified  BOOLEAN DEFAULT FALSE,
    email_verify_token VARCHAR(100),
    email_verify_expires TIMESTAMPTZ,
    is_active       BOOLEAN DEFAULT TRUE,
    last_login      TIMESTAMPTZ,
    password_reset_token VARCHAR(100),
    password_reset_expires TIMESTAMPTZ,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_applicants_email ON applicants(email);
CREATE INDEX idx_applicants_cycle ON applicants(cycle_id);
CREATE INDEX idx_applicants_region ON applicants(region);

-- ============================================================
-- TABLE: applications
-- ============================================================
CREATE TABLE applications (
    application_id  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    applicant_id    UUID NOT NULL UNIQUE REFERENCES applicants(applicant_id),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    -- Education
    education_level VARCHAR(50) NOT NULL,  -- WASSCE, SSSCE, HND, Degree, etc.
    school_name     VARCHAR(200),
    graduation_year INT,
    english_grade   VARCHAR(5),
    math_grade      VARCHAR(5),
    science_grade   VARCHAR(5),
    total_credits   INT,
    has_degree      BOOLEAN DEFAULT FALSE,
    degree_field    VARCHAR(200),
    -- Physical
    height_cm       DECIMAL(5,2) NOT NULL,
    weight_kg       DECIMAL(5,2),
    chest_cm        DECIMAL(5,2),
    -- Health Declarations
    has_medical_condition BOOLEAN DEFAULT FALSE,
    medical_condition_details TEXT,
    has_criminal_record BOOLEAN DEFAULT FALSE,
    criminal_record_details TEXT,
    -- Submission
    status          VARCHAR(30) NOT NULL DEFAULT 'draft'
                    CHECK (status IN (
                        'draft','submitted','documents_verified',
                        'eligible','ineligible','shortlisted',
                        'appointment_scheduled','screening_completed',
                        'selected','reserve_list','rejected'
                    )),
    submitted_at    TIMESTAMPTZ,
    last_status_update TIMESTAMPTZ DEFAULT NOW(),
    rejection_reason TEXT,
    internal_notes  TEXT,  -- admin-only notes
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_cycle ON applications(cycle_id);

-- ============================================================
-- TABLE: documents
-- ============================================================
CREATE TABLE documents (
    document_id     UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    applicant_id    UUID NOT NULL REFERENCES applicants(applicant_id),
    application_id  UUID NOT NULL REFERENCES applications(application_id),
    doc_type        VARCHAR(50) NOT NULL CHECK (doc_type IN (
                        'birth_certificate','educational_cert','national_id',
                        'passport_photo','medical_cert','other'
                    )),
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,  -- UUID-based stored name
    file_path       TEXT NOT NULL,
    file_size_bytes INT NOT NULL,
    mime_type       VARCHAR(100) NOT NULL,
    verification_status VARCHAR(20) DEFAULT 'pending'
                    CHECK (verification_status IN ('pending','verified','rejected','needs_reupload')),
    verified_by     UUID,  -- admin_id
    verified_at     TIMESTAMPTZ,
    rejection_reason TEXT,
    uploaded_at     TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_documents_applicant ON documents(applicant_id);
CREATE INDEX idx_documents_type ON documents(doc_type);

-- ============================================================
-- TABLE: eligibility_results
-- ============================================================
CREATE TABLE eligibility_results (
    result_id       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    application_id  UUID NOT NULL UNIQUE REFERENCES applications(application_id),
    applicant_id    UUID NOT NULL REFERENCES applicants(applicant_id),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    -- Individual criterion results (PASS/FAIL + detail)
    age_check       BOOLEAN NOT NULL,
    age_check_detail TEXT,          -- e.g. "Age: 23 years — PASS"
    nationality_check BOOLEAN NOT NULL,
    nationality_check_detail TEXT,
    education_check  BOOLEAN NOT NULL,
    education_check_detail TEXT,
    height_check    BOOLEAN NOT NULL,
    height_check_detail TEXT,
    criminal_check  BOOLEAN NOT NULL,
    criminal_check_detail TEXT,
    document_completeness_check BOOLEAN NOT NULL,
    document_completeness_detail TEXT,
    -- Overall
    overall_result  VARCHAR(20) NOT NULL CHECK (overall_result IN ('eligible','ineligible')),
    failure_reasons TEXT[],         -- Array of failed criterion labels
    score           INT DEFAULT 0,  -- Optional weighted score (0-100)
    evaluated_at    TIMESTAMPTZ DEFAULT NOW(),
    evaluated_by    VARCHAR(20) DEFAULT 'system' -- 'system' or admin_id
);

-- ============================================================
-- TABLE: verification_codes
-- ============================================================
CREATE TABLE verification_codes (
    code_id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    application_id  UUID NOT NULL UNIQUE REFERENCES applications(application_id),
    applicant_id    UUID NOT NULL REFERENCES applicants(applicant_id),
    code_value      VARCHAR(20) NOT NULL UNIQUE,  -- e.g. GAF-2024-A78X3K
    qr_code_path    TEXT,           -- path to generated QR image
    issued_at       TIMESTAMPTZ DEFAULT NOW(),
    expires_at      TIMESTAMPTZ NOT NULL,
    is_used         BOOLEAN DEFAULT FALSE,
    used_at         TIMESTAMPTZ,
    used_at_venue   VARCHAR(200),
    invalidated     BOOLEAN DEFAULT FALSE,
    invalidated_reason TEXT
);

CREATE INDEX idx_vcodes_code ON verification_codes(code_value);

-- ============================================================
-- TABLE: appointments
-- ============================================================
CREATE TABLE appointments (
    appointment_id  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    application_id  UUID NOT NULL UNIQUE REFERENCES applications(application_id),
    applicant_id    UUID NOT NULL REFERENCES applicants(applicant_id),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    slot_id         UUID REFERENCES appointment_slots(slot_id),
    venue           VARCHAR(300) NOT NULL,
    venue_address   TEXT,
    scheduled_date  DATE NOT NULL,
    scheduled_time  TIME NOT NULL,
    reporting_time  TIME,           -- e.g. 30 mins before scheduled time
    status          VARCHAR(20) DEFAULT 'confirmed'
                    CHECK (status IN ('confirmed','attended','absent','rescheduled','cancelled')),
    notification_sent BOOLEAN DEFAULT FALSE,
    notification_sent_at TIMESTAMPTZ,
    reminder_sent   BOOLEAN DEFAULT FALSE,
    notes           TEXT,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: appointment_slots
-- ============================================================
CREATE TABLE appointment_slots (
    slot_id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cycle_id        UUID NOT NULL REFERENCES recruitment_cycles(cycle_id),
    venue           VARCHAR(300) NOT NULL,
    slot_date       DATE NOT NULL,
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,
    capacity        INT NOT NULL DEFAULT 50,
    booked_count    INT DEFAULT 0,
    region          VARCHAR(100),   -- Filter by applicant region
    service_type    VARCHAR(20),    -- army / navy / airforce / all
    created_by      UUID,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: screening_results
-- ============================================================
CREATE TABLE screening_results (
    screening_id    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    application_id  UUID NOT NULL UNIQUE REFERENCES applications(application_id),
    appointment_id  UUID REFERENCES appointments(appointment_id),
    -- Physical Screening Scores
    medical_exam_result VARCHAR(20) CHECK (medical_exam_result IN ('pass','fail','pending')),
    medical_notes   TEXT,
    fitness_score   DECIMAL(5,2),
    fitness_result  VARCHAR(20) CHECK (fitness_result IN ('pass','fail','pending')),
    interview_score DECIMAL(5,2),
    interview_result VARCHAR(20) CHECK (interview_result IN ('pass','fail','pending')),
    -- Overall
    overall_screening_result VARCHAR(20) CHECK (overall_screening_result IN ('pass','fail','pending')),
    screened_by     UUID,           -- admin_id
    screened_at     TIMESTAMPTZ,
    notes           TEXT,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: administrators
-- ============================================================
CREATE TABLE administrators (
    admin_id        UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(255) UNIQUE NOT NULL,
    phone           VARCHAR(20),
    password_hash   VARCHAR(255) NOT NULL,
    role            VARCHAR(20) NOT NULL DEFAULT 'admin'
                    CHECK (role IN ('super_admin','admin','viewer')),
    assigned_cycle  UUID REFERENCES recruitment_cycles(cycle_id),
    is_active       BOOLEAN DEFAULT TRUE,
    last_login      TIMESTAMPTZ,
    created_by      UUID,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE notifications (
    notification_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    applicant_id    UUID REFERENCES applicants(applicant_id),
    admin_id        UUID REFERENCES administrators(admin_id),
    type            VARCHAR(30) NOT NULL CHECK (type IN (
                        'registration','submission','eligibility_pass',
                        'eligibility_fail','shortlisting','appointment',
                        'screening_reminder','selection','reserve_list',
                        'rejection','verification_code','system'
                    )),
    channel         VARCHAR(10) NOT NULL CHECK (channel IN ('email','sms','both')),
    recipient_email VARCHAR(255),
    recipient_phone VARCHAR(20),
    subject         VARCHAR(255),
    message         TEXT NOT NULL,
    status          VARCHAR(20) DEFAULT 'pending'
                    CHECK (status IN ('pending','sent','failed','retrying')),
    attempt_count   INT DEFAULT 0,
    last_attempt_at TIMESTAMPTZ,
    sent_at         TIMESTAMPTZ,
    error_log       TEXT,
    metadata        JSONB,          -- Extra context (verification code, venue, etc.)
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_notifications_applicant ON notifications(applicant_id);
CREATE INDEX idx_notifications_status ON notifications(status);

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE audit_logs (
    log_id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    actor_type      VARCHAR(20) NOT NULL CHECK (actor_type IN ('admin','super_admin','system','applicant')),
    actor_id        UUID,
    action          VARCHAR(100) NOT NULL,
    target_table    VARCHAR(100),
    target_id       UUID,
    old_value       JSONB,
    new_value       JSONB,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_audit_actor ON audit_logs(actor_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);

-- ============================================================
-- TABLE: system_settings
-- ============================================================
CREATE TABLE system_settings (
    setting_key     VARCHAR(100) PRIMARY KEY,
    setting_value   TEXT NOT NULL,
    setting_type    VARCHAR(20) DEFAULT 'string'
                    CHECK (setting_type IN ('string','integer','boolean','json')),
    description     TEXT,
    updated_by      UUID,
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- Seed default settings
INSERT INTO system_settings VALUES
('app_name', 'DMRMS', 'string', 'Application name', NULL, NOW()),
('sms_enabled', 'true', 'boolean', 'Enable SMS sending', NULL, NOW()),
('email_enabled', 'true', 'boolean', 'Enable email sending', NULL, NOW()),
('max_upload_size_mb', '5', 'integer', 'Max file upload size in MB', NULL, NOW()),
('allowed_file_types', '["pdf","jpg","jpeg","png"]', 'json', 'Allowed document formats', NULL, NOW()),
('session_timeout_minutes', '30', 'integer', 'Session timeout', NULL, NOW()),
('max_login_attempts', '5', 'integer', 'Before lockout', NULL, NOW()),
('voucher_price_ghs', '350', 'integer', 'Voucher price in GHS', NULL, NOW()),
('verification_code_expiry_hours', '72', 'integer', 'Code validity period', NULL, NOW());
```

---

## 5. VISUAL DESIGN SYSTEM

### 5.1 Brand Identity

**Inspired by:** gafonline.mil.gh (navy + white), apply.mil.gh (clean card layout)  
**Direction:** "Official Authority meets Modern Digital Service" — feels military-grade serious but accessible, not bureaucratic and frustrating.

### 5.2 Color Palette

```css
:root {
  /* Primary — Ghana Armed Forces Navy (from gafonline.mil.gh theme: #061948) */
  --color-navy-darkest:   #061948;  /* deepest bg, navbar */
  --color-navy-dark:      #0A2560;  /* card headers, sidebars */
  --color-navy-mid:       #0F3480;  /* hover states, borders */
  --color-navy-light:     #1A4BA0;  /* secondary buttons */

  /* Accent — Ghana Flag Gold */
  --color-gold:           #C8A000;  /* Ghana gold — primary CTA */
  --color-gold-bright:    #E8B800;  /* hover gold */
  --color-gold-muted:     #A07800;  /* text on light bg */
  --color-gold-light:     #FFF3C0;  /* gold tint backgrounds */

  /* Ghana Flag Red (used sparingly for alerts/danger) */
  --color-red:            #CC0000;
  --color-red-light:      #FFE5E5;

  /* Ghana Flag Green (success states) */
  --color-green:          #006B3F;
  --color-green-light:    #E5F5EE;

  /* Neutrals */
  --color-white:          #FFFFFF;
  --color-off-white:      #F8F9FC;
  --color-gray-50:        #F3F4F8;
  --color-gray-100:       #E8EAF0;
  --color-gray-300:       #BEC3D0;
  --color-gray-500:       #7A8099;
  --color-gray-700:       #3D4260;
  --color-gray-900:       #1A1E30;

  /* Status Colors */
  --color-status-pending:   #E8A000;
  --color-status-eligible:  #006B3F;
  --color-status-ineligible: #CC0000;
  --color-status-shortlisted: #0F3480;
  --color-status-selected:  #006B3F;
  --color-status-rejected:  #CC0000;

  /* Gradients */
  --gradient-hero: linear-gradient(135deg, #061948 0%, #0A2560 50%, #0F3480 100%);
  --gradient-card: linear-gradient(180deg, #0A2560 0%, #061948 100%);
  --gradient-gold: linear-gradient(90deg, #C8A000 0%, #E8B800 100%);
  --gradient-overlay: linear-gradient(180deg, rgba(6,25,72,0) 0%, rgba(6,25,72,0.9) 100%);
}
```

### 5.3 Typography

```css
/* Import in HTML head */
/* <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet"> */

:root {
  /* Display — Military/Authoritative feel */
  --font-display: 'Barlow Condensed', 'Arial Narrow', sans-serif;
  /* Body — Clean, legible */
  --font-body: 'Inter', 'Segoe UI', system-ui, sans-serif;
  /* Data/Code — Monospace for codes, numbers */
  --font-mono: 'JetBrains Mono', 'Consolas', monospace;

  /* Type Scale */
  --text-xs:   0.75rem;    /* 12px */
  --text-sm:   0.875rem;   /* 14px */
  --text-base: 1rem;       /* 16px */
  --text-lg:   1.125rem;   /* 18px */
  --text-xl:   1.25rem;    /* 20px */
  --text-2xl:  1.5rem;     /* 24px */
  --text-3xl:  1.875rem;   /* 30px */
  --text-4xl:  2.25rem;    /* 36px */
  --text-5xl:  3rem;       /* 48px */
  --text-6xl:  3.75rem;    /* 60px */
  --text-hero: clamp(3rem, 8vw, 6rem); /* Fluid hero size */
}
```

### 5.4 Spacing & Layout

```css
:root {
  /* Spacing Scale */
  --space-1:  0.25rem;   /* 4px */
  --space-2:  0.5rem;    /* 8px */
  --space-3:  0.75rem;   /* 12px */
  --space-4:  1rem;      /* 16px */
  --space-6:  1.5rem;    /* 24px */
  --space-8:  2rem;      /* 32px */
  --space-12: 3rem;      /* 48px */
  --space-16: 4rem;      /* 64px */
  --space-24: 6rem;      /* 96px */

  /* Border Radius */
  --radius-sm:  4px;
  --radius-md:  8px;
  --radius-lg:  12px;
  --radius-xl:  16px;
  --radius-2xl: 24px;
  --radius-full: 9999px;

  /* Shadows */
  --shadow-sm:  0 1px 3px rgba(6,25,72,0.12);
  --shadow-md:  0 4px 16px rgba(6,25,72,0.16);
  --shadow-lg:  0 8px 32px rgba(6,25,72,0.20);
  --shadow-xl:  0 16px 48px rgba(6,25,72,0.24);
  --shadow-gold: 0 4px 20px rgba(200,160,0,0.30);

  /* Transitions */
  --transition-fast:   150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow:   500ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

### 5.5 Component Tokens

```css
/* Buttons */
.btn-primary {
  background: var(--gradient-gold);
  color: var(--color-navy-darkest);
  font-family: var(--font-display);
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: var(--space-3) var(--space-8);
  border-radius: var(--radius-sm);
  border: none;
  cursor: pointer;
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-gold);
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 28px rgba(200,160,0,0.45);
}

.btn-secondary {
  background: transparent;
  color: var(--color-gold);
  border: 2px solid var(--color-gold);
  /* same padding, radius */
}

.btn-ghost {
  background: rgba(255,255,255,0.08);
  color: var(--color-white);
  border: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(8px);
}

/* Cards */
.card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-gray-100);
  box-shadow: var(--shadow-sm);
  transition: box-shadow var(--transition-normal), transform var(--transition-normal);
}
.card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-2px);
}

.card-dark {
  background: var(--color-navy-dark);
  border: 1px solid rgba(255,255,255,0.08);
  color: var(--color-white);
}

/* Status Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}
.badge-eligible    { background: var(--color-green-light); color: var(--color-green); }
.badge-ineligible  { background: var(--color-red-light); color: var(--color-red); }
.badge-pending     { background: var(--color-gold-light); color: var(--color-gold-muted); }
.badge-shortlisted { background: #E5ECFF; color: var(--color-navy-mid); }
.badge-selected    { background: var(--color-green-light); color: var(--color-green); }
.badge-rejected    { background: var(--color-red-light); color: var(--color-red); }

/* Form Elements */
.form-input {
  width: 100%;
  padding: var(--space-3) var(--space-4);
  border: 1.5px solid var(--color-gray-300);
  border-radius: var(--radius-md);
  font-family: var(--font-body);
  font-size: var(--text-base);
  color: var(--color-gray-900);
  background: var(--color-white);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
  outline: none;
}
.form-input:focus {
  border-color: var(--color-navy-mid);
  box-shadow: 0 0 0 3px rgba(15,52,128,0.15);
}
.form-input.error {
  border-color: var(--color-red);
  box-shadow: 0 0 0 3px rgba(204,0,0,0.10);
}
```

### 5.6 Animations Specification

```css
/* animations.css — all keyframes */

/* Fade Up — standard section reveal */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Fade In — simple opacity */
@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}

/* Slide In Left */
@keyframes slideInLeft {
  from { opacity: 0; transform: translateX(-60px); }
  to   { opacity: 1; transform: translateX(0); }
}

/* Slide In Right */
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(60px); }
  to   { opacity: 1; transform: translateX(0); }
}

/* Scale Pop */
@keyframes scalePop {
  from { opacity: 0; transform: scale(0.85); }
  to   { opacity: 1; transform: scale(1); }
}

/* Pulse Gold — for CTA buttons */
@keyframes pulseGold {
  0%, 100% { box-shadow: 0 4px 20px rgba(200,160,0,0.30); }
  50%       { box-shadow: 0 4px 36px rgba(200,160,0,0.65); }
}

/* Progress Bar Fill */
@keyframes fillBar {
  from { width: 0%; }
  to   { width: var(--target-width); }
}

/* Counter Number Animation — handled by JS */
/* Shimmer Loading Skeleton */
@keyframes shimmer {
  from { background-position: -200% 0; }
  to   { background-position: 200% 0; }
}
.skeleton {
  background: linear-gradient(90deg,
    var(--color-gray-100) 25%,
    var(--color-gray-50) 50%,
    var(--color-gray-100) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  border-radius: var(--radius-md);
}

/* Reduced Motion Fallback */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## 6. PUBLIC-FACING PAGES

### 6.1 Landing Page (`index.php` → `views/public/landing.php`)

#### Hero Section

Full-viewport hero with parallax background. The background is a **dark navy overlay over a photo of Ghana Armed Forces personnel in formation** (provided as a local image). Animated particle effect (CSS only — no canvas needed) adds subtle depth.

```
┌─────────────────────────────────────────────────────────────────┐
│  [GAF LOGO] DMRMS            [About][Requirements][FAQs][Login] │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│     ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░            │
│                                                                  │
│     GHANA ARMED FORCES                                           │
│     DIGITAL RECRUITMENT                          [PHOTO BG]     │
│     MANAGEMENT SYSTEM                                            │
│                                                                  │
│     Transparent • Automated • Efficient                          │
│                                                                  │
│     [APPLY NOW ▶]          [VIEW REQUIREMENTS]                  │
│                                                                  │
│     ▼ SCROLL TO EXPLORE                                          │
└─────────────────────────────────────────────────────────────────┘
```

**GSAP Animation Sequence (landing.js):**
```javascript
// landing.js — GSAP ScrollTrigger sequences
gsap.registerPlugin(ScrollTrigger);

const heroTL = gsap.timeline({ delay: 0.3 });
heroTL
  .from('.hero-eyebrow', { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' })
  .from('.hero-title', { y: 60, opacity: 0, duration: 0.8, ease: 'power3.out' }, '-=0.3')
  .from('.hero-subtitle', { y: 30, opacity: 0, duration: 0.6, ease: 'power2.out' }, '-=0.4')
  .from('.hero-cta', { y: 20, opacity: 0, duration: 0.5, stagger: 0.15, ease: 'back.out(1.7)' }, '-=0.3')
  .from('.hero-scroll-hint', { opacity: 0, duration: 0.5 }, '-=0.1');

// Stats counter animation when scrolled into view
gsap.utils.toArray('.stat-number').forEach(el => {
  const target = parseInt(el.dataset.target);
  ScrollTrigger.create({
    trigger: el,
    start: 'top 80%',
    once: true,
    onEnter: () => {
      gsap.to({ val: 0 }, {
        val: target,
        duration: 2,
        ease: 'power2.out',
        onUpdate: function() { el.textContent = Math.round(this.targets()[0].val).toLocaleString(); }
      });
    }
  });
});

// Section reveals
gsap.utils.toArray('.reveal-section').forEach(section => {
  gsap.from(section, {
    scrollTrigger: { trigger: section, start: 'top 85%', toggleActions: 'play none none none' },
    y: 60, opacity: 0, duration: 0.8, ease: 'power3.out'
  });
});

// Feature cards stagger
gsap.utils.toArray('.feature-card').forEach((card, i) => {
  gsap.from(card, {
    scrollTrigger: { trigger: card, start: 'top 88%', toggleActions: 'play none none none' },
    y: 40, opacity: 0, duration: 0.6, delay: i * 0.1, ease: 'power2.out'
  });
});

// Parallax hero background
gsap.to('.hero-bg', {
  scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true },
  y: '30%', ease: 'none'
});
```

#### Statistics Strip

Animated number counters triggered on scroll:
- Applications Processed: **12,450+**
- Recruitment Cycles: **8**
- Eligible Candidates: **4,200+**
- Processing Time Reduction: **80%**

#### How It Works — Steps Section

Visual 4-step process (horizontal on desktop, vertical on mobile):
```
[1] Purchase Voucher → [2] Register & Apply → [3] Eligibility Check → [4] Get Admitted
```

Each step has: icon, title, description, connecting animated line.

#### Features Section — 4 Feature Cards

- **Automated Screening** — 6-point eligibility engine
- **Real-Time Notifications** — SMS + Email at every stage
- **Secure Document Upload** — Encrypted, verified storage
- **Analytics Dashboard** — Live recruitment intelligence

#### Eligibility Preview Section

Dark navy card listing requirements (same info as eligibility_info.php) with a gold "Check if You Qualify" CTA.

#### Latest Announcements

Pull from DB: 3 most recent published announcements with date and category badge.

#### Join CTA Section

Full-width dark section: "Ready to Serve Ghana?" with hero image strip and Apply Now button.

#### Footer

Four-column: Logo + tagline | Quick Links | Contact | Social Media (Facebook, Twitter, Instagram, YouTube — matching GAF's actual social profiles structure). Bottom bar: "© 2024 DMRMS — Digital Military Recruitment Management System | University of Mines and Technology, Tarkwa"

### 6.2 Eligibility Requirements Page

Structured accordion/expandable layout covering:
- Age requirements (18–26 years)
- Educational qualifications (WASSCE minimum credits, officer paths)
- Physical standards (height by gender and service type)
- Nationality and citizenship
- Criminal record declaration
- Medical fitness
- Document checklist

At the bottom: "Apply Now" CTA + "Voucher Purchasing" info.

### 6.3 Public Login Page

Clean two-panel design:
- **Left:** Dark navy panel with GAF logo, tagline, and a quote about service
- **Right:** White login form with:
  - Email + password fields
  - "Login as Admin" vs "Login as Applicant" tab switcher
  - Forgot password link
  - Register link for new applicants

On mobile: single column, logo at top.

### 6.4 Announcements Page

Card grid of all active announcements. Each card: category badge (GENERAL RECRUITMENT / OFFICER CADETS / SPECIAL FORCES), title, date, excerpt, "Read More" link. Filter by category and year.

### 6.5 FAQ Page

Accordion (Alpine.js `x-show` + transition) with 15+ questions covering:
- Application process
- Voucher purchase
- Document requirements
- Physical screening
- Tracking applications
- Results and selection

### 6.6 Contact / Help Desk Page

- Contact form (name, email, category, message) → stores to DB + emails admin
- Office address (Burma Camp, Accra)
- Phone numbers
- Social media links
- Google Maps embed (iframe)

---

## 7. APPLICANT PORTAL

### 7.1 Registration Flow (Multi-Step)

The registration is gated by voucher validation. Steps:

**Step 1 — Voucher Entry**
```
┌──────────────────────────────────────┐
│  🎟️  Enter Your Voucher Details      │
│                                      │
│  Serial Number: [__________________] │
│  PIN Code:      [__________________] │
│                                      │
│  [VALIDATE VOUCHER]                  │
│                                      │
│  How to get a voucher? [Learn More]  │
└──────────────────────────────────────┘
```
- `POST /api/voucher/validate` → checks: exists, unused, not expired, cycle active
- On success: session stores `voucher_id`, advance to Step 2

**Step 2 — Personal Information**
Fields: First Name, Last Name, Other Names, Date of Birth, Gender, Nationality, Ghana Card Number, Phone (with +233 prefix selector), Email.
Real-time validation: phone format, email format, DOB range check (shows age instantly).

**Step 3 — Account Setup**
Password (with strength meter) + confirm password. Password requirements shown as checklist (8+ chars, uppercase, number, special char).

**Step 4 — Verification**
OTP sent to phone via SMS, 6-digit input with resend countdown timer (60 seconds). On success: account created, redirect to applicant dashboard.

### 7.2 Applicant Dashboard (`views/applicant/dashboard.php`)

```
┌──────────────────────────────────────────────────────────────────┐
│ [GAF Logo] DMRMS Applicant Portal          [👤 John Doe] [Logout] │
├───────────────┬──────────────────────────────────────────────────┤
│               │                                                   │
│  SIDEBAR      │   Welcome back, John! 👋                         │
│               │                                                   │
│  📊 Dashboard │   ┌─────────────────────────────────────────────┐│
│  📝 Application│   │  APPLICATION STATUS                         ││
│  📁 Documents  │   │                                             ││
│  📅 Appointment│   │  [●━━━━━━━━━━━━━━━━━━━━━━━━━━━━○]          ││
│  🎫 My Code   │   │  Registered → Submitted → Eligible →        ││
│  👤 Profile   │   │  Shortlisted → Scheduled → Selected         ││
│  ❓ Help      │   │                                             ││
│               │   │  Current: ELIGIBLE ✅                       ││
│               │   │  Next Step: Await Shortlisting              ││
│               │   └─────────────────────────────────────────────┘│
│               │                                                   │
│               │   ┌─────────┐ ┌─────────┐ ┌─────────────────┐   │
│               │   │📝       │ │📁       │ │🎫               │   │
│               │   │ Status  │ │ Docs    │ │ Verification     │   │
│               │   │Eligible │ │4/4 ✅   │ │ Code Ready      │   │
│               │   └─────────┘ └─────────┘ └─────────────────┘   │
│               │                                                   │
│               │   RECENT NOTIFICATIONS                           │
│               │   • Your application has been approved ✅ 2h ago  │
│               │   • Document "Birth Cert" verified ✅ 1d ago      │
└───────────────┴──────────────────────────────────────────────────┘
```

### 7.3 Application Form (`views/applicant/application_form.php`)

Multi-step form wizard with:
- **Progress indicator** at top (step dots, labels, percentage)
- **Auto-save draft** every 30 seconds (AJAX call to `/api/application/save-draft`)
- **Save & Exit** button visible at all times

**Section 1 — Personal Information**
First Name, Last Name, Date of Birth (auto-calculates age and shows validity), Gender, Nationality, Residential Address, Region (dropdown: all 16 Ghana regions), District, GPS Address, Emergency Contact Name and Phone.

**Section 2 — Educational Background**
Education Level (WASSCE/SSSCE/HND/Degree/Masters — dynamic options based on cycle config), School/Institution Name, Year of Graduation, Grade fields for English + Math + Science (for WASSCE/SSSCE applicants — shows/hides based on education level), Upload Educational Certificate button (links to document upload).

**Section 3 — Physical & Health**
Height in cm (with real-time foot/inch conversion displayed), Weight in kg, Chest measurement (cm), Preferred Service (Army/Navy/Air Force/Any), Medical condition declaration (Yes/No radio → conditional text area if Yes), Criminal record declaration (Yes/No radio → conditional text area + warning if Yes).

**Section 4 — Review & Submit**
Read-only summary of all entered data. Checklist: "I confirm all information is accurate and truthful." Submit button triggers eligibility check.

**Validation Rules:**
- All mandatory fields enforced on each step transition (client + server)
- Age: calculated from DOB, shown in real-time
- Height: numeric, range 1.00–2.50m
- Cannot submit if any required document is missing

### 7.4 Document Upload (`views/applicant/document_upload.php`)

```
┌──────────────────────────────────────────────────────────────┐
│  REQUIRED DOCUMENTS                                           │
│                                                              │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐  │
│  │ 🪪 BIRTH CERT  │  │ 🎓 EDUCATION   │  │ 🪪 NATIONAL  │  │
│  │                │  │    CERT        │  │    ID        │  │
│  │ ✅ Uploaded    │  │ ✅ Uploaded    │  │ ⏳ Pending   │  │
│  │ [Replace]      │  │ [Replace]      │  │ [UPLOAD ▲]   │  │
│  └────────────────┘  └────────────────┘  └──────────────┘  │
│                                                              │
│  ┌────────────────┐                                          │
│  │ 📸 PASSPORT    │                                          │
│  │    PHOTO       │   Drag & Drop any document here          │
│  │                │   or click a slot above to upload        │
│  │ ⏳ Pending     │                                          │
│  │ [UPLOAD ▲]     │   Accepted: PDF, JPG, PNG | Max: 5MB    │
│  └────────────────┘                                          │
│                                                              │
│  PROGRESS: [████████░░] 2 of 4 uploaded                     │
│                                                              │
│  [← Back to Form]                    [Continue to Review →] │
└──────────────────────────────────────────────────────────────┘
```

**Upload Logic:**
```javascript
// file-upload.js
const dropZone = document.querySelector('.drop-zone');
const slots = document.querySelectorAll('.doc-slot');

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-active'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-active'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('drag-active');
  handleFile(e.dataTransfer.files[0], activeDocType);
});

async function handleFile(file, docType) {
  // Client-side validation
  const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
  if (!allowed.includes(file.type)) return showToast('error', 'Invalid file type');
  if (file.size > 5 * 1024 * 1024) return showToast('error', 'File too large (max 5MB)');

  const formData = new FormData();
  formData.append('file', file);
  formData.append('doc_type', docType);
  formData.append('application_id', APP_ID);

  // Show progress bar
  showUploadProgress(docType, 0);
  const response = await fetch('/api/document/upload', {
    method: 'POST',
    body: formData,
    headers: { 'X-CSRF-Token': CSRF_TOKEN }
  });
  const result = await response.json();
  if (result.success) {
    updateSlotUI(docType, 'uploaded', result.filename);
    updateOverallProgress();
    showToast('success', 'Document uploaded successfully');
  }
}
```

**Server-side (document.php API):**
- Validate MIME type using `finfo_file()` (not just extension)
- Rename to `UUID.extension` before storage
- Store in `/uploads/{doc_type}/{applicant_id}/`
- Insert record to `documents` table
- Return success + stored filename

### 7.5 Application Status Timeline (`views/applicant/status_timeline.php`)

Vertical timeline showing all status milestones. Each milestone: icon, status label, date/time, description. Completed stages: filled gold circle. Current stage: pulsing gold animation. Future stages: grey dashed circle.

```
●  Registered                   25 Nov 2024, 10:42 AM
   Account created successfully

●  Application Submitted        26 Nov 2024, 2:15 PM
   Application #GAF-2024-78432 received

●  Documents Verified           27 Nov 2024, 9:00 AM
   All 4 documents verified by admin

◉  Eligibility Approved         27 Nov 2024, 9:05 AM  ← CURRENT (pulsing)
   All criteria passed. Shortlisting in progress.

○  Shortlisted                  Pending
   Waiting for administrator review

○  Appointment Scheduled        Pending

○  Physical Screening           Pending

○  Final Decision               Pending
```

### 7.6 Verification Code Page (`views/applicant/verification_code.php`)

Only visible once status = `shortlisted`. Shows:
- Large styled code display: `GAF-2024-A78X3K`
- QR code image (generated by QR service)
- Expiry date
- Instructions for use at screening venue
- "Download as PDF" button (generates admission-letter style document)
- "Print" button

### 7.7 Appointment Page (`views/applicant/appointment.php`)

Shows:
- Venue name and address (map embed)
- Date and time
- Reporting time (30 minutes before)
- What to bring (list)
- Verification code reminder
- "Add to Calendar" buttons (Google Calendar / ICS download)

---

## 8. RECRUITMENT ADMIN PORTAL

### 8.1 Admin Dashboard (`views/admin/dashboard.php`)

```
┌────────────────────────────────────────────────────────────────────┐
│ [GAF Logo] DMRMS Admin         Cycle: 2024 Intake    [Admin Jones] │
├──────────────┬─────────────────────────────────────────────────────┤
│              │                                                      │
│  SIDEBAR     │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────┐ │
│              │  │  2,450   │ │  1,820   │ │   420    │ │  210   │ │
│  📊 Dashboard│  │ TOTAL    │ │ ELIGIBLE │ │SHORTLIST.│ │SELECTED│ │
│  👥 Candidates│  │   Apps   │ │          │ │          │ │        │ │
│  📁 Documents │  └──────────┘ └──────────┘ └──────────┘ └────────┘ │
│  ✅ Shortlist │                                                      │
│  📅 Schedule  │  ┌──────────────────────────┐ ┌───────────────────┐ │
│  📊 Reports   │  │  Applications Over Time  │ │ Regional Breakdown│ │
│  ⚙️  Cycles   │  │  [Chart.js Line Chart]   │ │ [Chart.js Donut] │ │
│  🔔 Notif.    │  └──────────────────────────┘ └───────────────────┘ │
│              │                                                      │
│              │  RECENT ACTIVITY                                     │
│              │  • 12 new applications submitted — 2h ago            │
│              │  • Batch shortlisting completed (420 candidates)     │
│              │  • 15 appointment notifications sent — 1h ago        │
└──────────────┴─────────────────────────────────────────────────────┘
```

**Chart.js Dashboards:**
```javascript
// dashboard.js
// 1. Applications Over Time (Line Chart)
new Chart(document.getElementById('appTimelineChart'), {
  type: 'line',
  data: {
    labels: timeLabels,  // fetched from API
    datasets: [{
      label: 'Total Applications',
      data: appCounts,
      borderColor: '#0F3480',
      backgroundColor: 'rgba(15,52,128,0.08)',
      tension: 0.4, fill: true
    }, {
      label: 'Eligible',
      data: eligibleCounts,
      borderColor: '#006B3F',
      backgroundColor: 'rgba(0,107,63,0.06)',
      tension: 0.4, fill: true
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'top' } } }
});

// 2. Regional Distribution (Doughnut)
new Chart(document.getElementById('regionalChart'), {
  type: 'doughnut',
  data: {
    labels: regions,
    datasets: [{ data: regionCounts, backgroundColor: [...navyPalette] }]
  }
});

// 3. Status Pipeline (Horizontal Bar)
new Chart(document.getElementById('pipelineChart'), {
  type: 'bar',
  data: {
    labels: ['Submitted','Eligible','Shortlisted','Scheduled','Screened','Selected'],
    datasets: [{ data: pipelineCounts, backgroundColor: progressColors }]
  },
  options: { indexAxis: 'y', responsive: true }
});

// 4. Gender Distribution (Pie)
new Chart(document.getElementById('genderChart'), {
  type: 'pie',
  data: {
    labels: ['Male', 'Female'],
    datasets: [{ data: [malePct, femalePct], backgroundColor: ['#0F3480','#C8A000'] }]
  }
});
```

### 8.2 Candidates Table (`views/admin/candidates.php`)

Powerful filterable, sortable, searchable data table:

```
[Search...] [Status ▼] [Region ▼] [Service ▼] [Edu Level ▼]  [Export CSV] [Export PDF]

┌────┬──────────────────┬──────────┬────────┬──────┬────────────┬───────────┬─────────┐
│ ☐  │ Name             │ GAF ID   │Region  │Gender│ Edu Level  │ Status    │ Actions │
├────┼──────────────────┼──────────┼────────┼──────┼────────────┼───────────┼─────────┤
│ ☐  │ Kwame Asante     │GAF-78432 │Ashanti │ Male │ WASSCE     │ ● Eligible│ [View]  │
│ ☐  │ Ama Owusu        │GAF-78433 │Greater │Female│ WASSCE     │ ● Shortl. │ [View]  │
│ ☐  │ Kofi Mensah      │GAF-78434 │Volta   │ Male │ HND        │ ● Pending │ [View]  │
└────┴──────────────────┴──────────┴────────┴──────┴────────────┴───────────┴─────────┘
          Showing 1-20 of 2,450 applicants                [← 1 2 3 ... 123 →]

Bulk Actions: [☐ Select All]  [Shortlist Selected]  [Send Notification]  [Export Selected]
```

**Admin controls per row:**
- View full profile
- Verify documents
- Update status manually
- Add internal note
- Generate/revoke verification code
- Schedule appointment

### 8.3 Candidate Detail View (`views/admin/candidate_detail.php`)

Full profile split into tabs:
- **Personal** — all personal + contact info
- **Application** — form data, education, physical
- **Documents** — preview each document inline (PDF viewer, image viewer), verify/reject each with reason
- **Eligibility** — result breakdown (all 6 criteria with pass/fail)
- **Timeline** — full status history with timestamps
- **Appointments** — assigned slot details
- **Notifications** — all notifications sent to this applicant

Admin action panel (sticky right sidebar):
- Change status dropdown
- Add note (textarea)
- Send custom notification
- Shortlist / Remove from shortlist
- Assign appointment

### 8.4 Shortlisting Interface (`views/admin/shortlisting.php`)

Shows all eligible candidates with:
- Vacancies counter (remaining vs filled)
- Filter by service, region, education level
- Sort by score (if weighted scoring enabled)
- Bulk select + "Shortlist Selected" button
- Visual capacity meter (e.g., "210 of 350 positions filled")

When shortlisting is triggered:
1. Status updated to `shortlisted`
2. Verification code auto-generated
3. QR code generated
4. Notification queued (SMS + Email)

### 8.5 Appointment Scheduling (`views/admin/scheduling.php`)

**Slot Creator:**
```
Venue: [__________________________]
Date:  [📅 Date Picker]
Time:  [🕐 Start] to [🕐 End]
Capacity: [____]
Region Filter: [All / Specific region ▼]
Service: [All / Army / Navy / Air Force ▼]

[CREATE SLOT]
```

**Slot Overview:** Calendar-style or table view. Each slot shows: date, time, venue, capacity, booked count (progress bar), status (open/full/closed).

**Applicant Assignment:** System auto-assigns shortlisted applicants to slots based on region and availability, or admin assigns manually. On assignment: appointment confirmation sent immediately.

### 8.6 Reports & Analytics (`views/admin/reports.php`)

Tabbed interface:
- **Overview** — KPI cards + summary charts (Chart.js)
- **Applications Report** — Table with filters, exportable
- **Eligibility Report** — Pass/fail breakdown per criterion
- **Regional Report** — Map-style visualization of applications by region
- **Gender Report** — Distribution charts
- **Cycle Comparison** — Compare current vs previous cycles

Export options: **CSV** (PHP array to CSV) and **PDF** (mPDF formatted report with GAF header/footer).

---

## 9. SUPER ADMIN PORTAL

### 9.1 System Dashboard

Extends admin dashboard with additional metrics:
- Active administrator count
- Database size / storage usage
- Last backup status
- Failed login attempts (last 24h)
- Queue health (pending notifications)
- System uptime

### 9.2 User Management (`views/superadmin/users.php`)

CRUD for administrator accounts:
- Create admin with role (admin/viewer)
- Assign to specific recruitment cycle
- Deactivate/reactivate accounts
- Reset passwords
- View login history

### 9.3 Recruitment Cycle Management

Create/Edit/Archive cycles:
- Cycle name and description
- Date range + application window
- Vacancy counts (total + per service branch)
- Eligibility thresholds (min age, max age, height standards)
- Publish/unpublish control

### 9.4 System Settings (`views/superadmin/system_settings.php`)

Key-value settings editor:
- Enable/disable SMS
- Enable/disable email
- Set max upload size
- Set session timeout
- Set voucher price
- Set verification code expiry

### 9.5 Audit Logs (`views/superadmin/audit_logs.php`)

Full searchable/filterable audit trail:
- Who, what, when, which record
- Old vs new values (JSONB diff)
- IP address and user agent
- Export to CSV

### 9.6 Backup Management (`views/superadmin/backup.php`)

- Trigger manual DB backup (`pg_dump` via `exec()`)
- List existing backups (file, size, date)
- Download backup file
- Restore from backup (with confirmation modal)
- Schedule automated backup (writes to cron or PHP cron emulation)

---

## 10. BACKEND PHP API LAYER

### 10.1 PHP PDO Database Connection

```php
<?php
// config/database.php
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = [
                'host'     => getenv('DB_HOST') ?: 'localhost',
                'port'     => getenv('DB_PORT') ?: '5432',
                'dbname'   => getenv('DB_NAME') ?: 'dmrms_db',
                'user'     => getenv('DB_USER') ?: 'postgres',
                'password' => getenv('DB_PASS') ?: '',
            ];
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
            try {
                self::$instance = new PDO($dsn, $config['user'], $config['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,   // Critical: real prepared statements
                ]);
            } catch (PDOException $e) {
                error_log("DB Connection Failed: " . $e->getMessage());
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed']));
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
```

### 10.2 Base Model

```php
<?php
// core/Model.php
abstract class Model {
    protected PDO $db;
    protected string $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function findById(string $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as cnt";
        $total = $this->query($countSql, $params)[0]['total'] ?? 0;
        $data = $this->query("{$sql} LIMIT {$perPage} OFFSET {$offset}", $params);
        return [
            'data'        => $data,
            'total'       => (int) $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => (int) ceil($total / $perPage),
        ];
    }
}
```

### 10.3 Authentication System

```php
<?php
// config/session.php
class Auth {
    private const TOKEN_SECRET = 'DMRMS_SECRET_CHANGE_IN_PRODUCTION'; // Load from .env

    public static function login(string $email, string $password, string $role): bool|array {
        // 1. Fetch user by email + role
        // 2. Verify password with password_verify()
        // 3. Check failed attempts (rate limiting)
        // 4. Generate session token
        $token = self::generateToken($userId, $role);
        $_SESSION['dmrms_token'] = $token;
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
        $_SESSION['last_activity'] = time();
        return ['success' => true, 'redirect' => self::getDashboardUrl($role)];
    }

    private static function generateToken(string $userId, string $role): string {
        $payload = json_encode([
            'uid'  => $userId,
            'role' => $role,
            'exp'  => time() + (30 * 60), // 30 min
            'iat'  => time(),
        ]);
        return base64_encode($payload) . '.' . hash_hmac('sha256', $payload, self::TOKEN_SECRET);
    }

    public static function verify(): bool {
        if (!isset($_SESSION['dmrms_token'], $_SESSION['last_activity'])) return false;
        if ((time() - $_SESSION['last_activity']) > 1800) {
            self::logout();
            return false;
        }
        [$encodedPayload, $sig] = explode('.', $_SESSION['dmrms_token'], 2);
        $payload = base64_decode($encodedPayload);
        $expectedSig = hash_hmac('sha256', $payload, self::TOKEN_SECRET);
        if (!hash_equals($expectedSig, $sig)) return false;
        $_SESSION['last_activity'] = time(); // Refresh
        return true;
    }

    public static function requireRole(string ...$roles): void {
        if (!self::verify() || !in_array($_SESSION['role'], $roles)) {
            if (self::isApiRequest()) {
                http_response_code(401);
                die(json_encode(['error' => 'Unauthorized']));
            }
            header('Location: /login?error=unauthorized');
            exit;
        }
    }

    public static function logout(): void {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
}
```

### 10.4 API Routing

```php
<?php
// api/index.php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// CSRF protection for state-changing requests
if (in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','DELETE','PATCH'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token']));
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$segments = explode('/', trim(str_replace('/api', '', $path), '/'));
$resource = $segments[0] ?? '';

// Route mapping
$routes = [
    'auth'        => 'AuthController',
    'applicant'   => 'ApplicantController',
    'application' => 'ApplicationController',
    'document'    => 'DocumentController',
    'eligibility' => 'EligibilityController',
    'voucher'     => 'VoucherController',
    'admin'       => 'AdminController',
    'schedule'    => 'SchedulingController',
    'report'      => 'ReportController',
    'superadmin'  => 'SuperAdminController',
    'notification'=> 'NotificationController',
];

if (!isset($routes[$resource])) {
    http_response_code(404);
    die(json_encode(['error' => 'Endpoint not found']));
}

require_once __DIR__ . "/../controllers/{$routes[$resource]}.php";
$controller = new $routes[$resource]();
$action = $segments[1] ?? 'index';
if (method_exists($controller, $action)) {
    $controller->{$action}();
} else {
    http_response_code(404);
    die(json_encode(['error' => "Action '{$action}' not found"]));
}
```

### 10.5 Key API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/login` | None | Login applicant or admin |
| POST | `/api/auth/logout` | Session | Logout |
| POST | `/api/auth/forgot-password` | None | Request password reset |
| POST | `/api/voucher/validate` | None | Validate voucher code |
| POST | `/api/applicant/register` | Voucher | Create new applicant account |
| POST | `/api/applicant/verify-otp` | Session | Verify OTP |
| GET | `/api/applicant/dashboard` | Applicant | Dashboard data |
| POST | `/api/application/save-draft` | Applicant | Auto-save form progress |
| POST | `/api/application/submit` | Applicant | Final submission |
| GET | `/api/application/status` | Applicant | Current status + history |
| POST | `/api/document/upload` | Applicant | Upload a document |
| GET | `/api/document/{id}` | Applicant/Admin | Get document info |
| POST | `/api/eligibility/run` | System/Admin | Trigger eligibility check |
| GET | `/api/eligibility/result/{appId}` | Applicant/Admin | View result |
| GET | `/api/admin/candidates` | Admin | Paginated candidates list |
| GET | `/api/admin/candidate/{id}` | Admin | Single candidate detail |
| PUT | `/api/admin/candidate/{id}/status` | Admin | Update status |
| POST | `/api/admin/shortlist` | Admin | Bulk shortlist |
| POST | `/api/admin/shortlist/{id}/revoke` | Admin | Remove from shortlist |
| GET | `/api/admin/slots` | Admin | Get appointment slots |
| POST | `/api/admin/slots` | Admin | Create appointment slot |
| POST | `/api/admin/slots/{id}/assign` | Admin | Assign applicants to slot |
| GET | `/api/report/overview` | Admin | Dashboard KPIs |
| GET | `/api/report/export?format=csv` | Admin | Export CSV |
| GET | `/api/report/export?format=pdf` | Admin | Export PDF |
| POST | `/api/notification/send` | Admin | Manual notification |
| GET | `/api/notification/queue` | Admin | View notification queue |
| POST | `/api/notification/retry/{id}` | Admin | Retry failed notification |
| GET | `/api/superadmin/audit-logs` | Super Admin | Audit log data |
| GET | `/api/superadmin/users` | Super Admin | Admin users list |
| POST | `/api/superadmin/users` | Super Admin | Create admin user |
| GET | `/api/superadmin/settings` | Super Admin | Get system settings |
| PUT | `/api/superadmin/settings` | Super Admin | Update settings |
| POST | `/api/superadmin/backup` | Super Admin | Trigger DB backup |

---

## 11. ELIGIBILITY DECISION ENGINE

### 11.1 PHP Implementation (`EligibilityController.php`)

The engine is a sequential, short-circuit evaluator. Each criterion must pass before the next is checked. A failure records the reason but continues checking all criteria (to give the applicant full feedback, not just the first failure).

```php
<?php
// controllers/EligibilityController.php

class EligibilityController extends Controller {

    public function run(): void {
        Auth::requireRole('admin', 'super_admin', 'system');
        $data = json_decode(file_get_contents('php://input'), true);
        $applicationId = $this->sanitize($data['application_id'] ?? '');

        // Load application + applicant data
        $appModel = new ApplicationModel();
        $app = $appModel->getFullApplication($applicationId);
        if (!$app) { $this->jsonError('Application not found', 404); return; }

        // Load cycle requirements
        $cycleModel = new RecruitmentCycleModel();
        $cycle = $cycleModel->findById($app['cycle_id']);

        // Run all checks
        $result = $this->runChecks($app, $cycle);

        // Store result
        $eligModel = new EligibilityModel();
        $eligModel->saveResult($applicationId, $result);

        // Update application status
        $newStatus = $result['overall'] === 'eligible' ? 'eligible' : 'ineligible';
        $appModel->updateStatus($applicationId, $newStatus, implode('; ', $result['failures']));

        // Queue notification
        $notifController = new NotificationController();
        $notifController->queueEligibilityNotification($app['applicant_id'], $result);

        $this->json(['success' => true, 'result' => $result]);
    }

    private function runChecks(array $app, array $cycle): array {
        $checks   = [];
        $failures = [];

        // ── CHECK 1: Age ──────────────────────────────────────────────
        $dob     = new DateTime($app['date_of_birth']);
        $today   = new DateTime();
        $age     = (int) $today->diff($dob)->y;
        $agePass = ($age >= $cycle['min_age'] && $age <= $cycle['max_age']);
        $checks['age_check']        = $agePass;
        $checks['age_check_detail'] = "Age: {$age} years (Required: {$cycle['min_age']}–{$cycle['max_age']})";
        if (!$agePass) $failures[] = "Age not met ({$age} years)";

        // ── CHECK 2: Nationality ──────────────────────────────────────
        $nationalityPass             = (bool) $app['is_ghanaian_citizen'];
        $checks['nationality_check'] = $nationalityPass;
        $checks['nationality_check_detail'] = $nationalityPass
            ? 'Ghanaian citizenship confirmed'
            : 'Not a Ghanaian citizen by birth';
        if (!$nationalityPass) $failures[] = 'Not a Ghanaian citizen';

        // ── CHECK 3: Education ────────────────────────────────────────
        $eduPass             = $this->checkEducation($app, $cycle);
        $checks['education_check']        = $eduPass['pass'];
        $checks['education_check_detail'] = $eduPass['detail'];
        if (!$eduPass['pass']) $failures[] = 'Education requirements not met';

        // ── CHECK 4: Height ───────────────────────────────────────────
        $minHeight  = $app['gender'] === 'male' ? $cycle['min_height_male'] : $cycle['min_height_female'];
        $heightCm   = (float) $app['height_cm'];
        $heightPass = $heightCm >= ($minHeight * 100);
        $checks['height_check']        = $heightPass;
        $checks['height_check_detail'] = sprintf(
            'Height: %.0fcm (Required: ≥%.0fcm for %s)',
            $heightCm, $minHeight * 100, $app['gender']
        );
        if (!$heightPass) $failures[] = 'Minimum height not met';

        // ── CHECK 5: Criminal Record ──────────────────────────────────
        $criminalPass             = !(bool) $app['has_criminal_record'];
        $checks['criminal_check'] = $criminalPass;
        $checks['criminal_check_detail'] = $criminalPass
            ? 'No criminal record declared'
            : 'Criminal record declared — ineligible';
        if (!$criminalPass) $failures[] = 'Criminal record declared';

        // ── CHECK 6: Document Completeness ────────────────────────────
        $docModel      = new DocumentModel();
        $uploadedTypes = $docModel->getVerifiedDocTypes($app['application_id']);
        $requiredTypes = ['birth_certificate', 'educational_cert', 'national_id', 'passport_photo'];
        $missingDocs   = array_diff($requiredTypes, $uploadedTypes);
        $docsPass      = empty($missingDocs);
        $checks['document_completeness_check']  = $docsPass;
        $checks['document_completeness_detail'] = $docsPass
            ? 'All required documents uploaded and verified'
            : 'Missing documents: ' . implode(', ', $missingDocs);
        if (!$docsPass) $failures[] = 'Incomplete documents: ' . implode(', ', $missingDocs);

        // ── FINAL DECISION ────────────────────────────────────────────
        $overall = empty($failures) ? 'eligible' : 'ineligible';
        // Optional weighted score (0–100)
        $score = $this->computeScore($checks);

        return array_merge($checks, [
            'overall'  => $overall,
            'failures' => $failures,
            'score'    => $score,
        ]);
    }

    private function checkEducation(array $app, array $cycle): array {
        $level = strtoupper($app['education_level']);
        // WASSCE/SSSCE — need minimum credits
        if (in_array($level, ['WASSCE','SSSCE','GCE O-LEVEL'])) {
            $credits = (int) $app['total_credits'];
            $hasEnglish = $this->gradeIsCredit($app['english_grade']);
            $hasMath    = $this->gradeIsCredit($app['math_grade']);
            $pass = ($credits >= 6 && $hasEnglish);
            return [
                'pass'   => $pass,
                'detail' => "WASSCE: {$credits} credits, English: {$app['english_grade']}, Math: {$app['math_grade']}"
            ];
        }
        // HND, Degree — automatic education pass
        if (in_array($level, ['HND','DEGREE','MASTERS','PHD'])) {
            return ['pass' => true, 'detail' => "Higher qualification: {$level} accepted"];
        }
        return ['pass' => false, 'detail' => "Education level '{$level}' does not meet minimum requirements"];
    }

    private function gradeIsCredit(string $grade): bool {
        // WASSCE: A1, B2, B3, C4, C5, C6 = credits
        return in_array(strtoupper(trim($grade)), ['A1','B2','B3','C4','C5','C6','A','B','C']);
    }

    private function computeScore(array $checks): int {
        $weights = [
            'age_check' => 15, 'nationality_check' => 20,
            'education_check' => 25, 'height_check' => 15,
            'criminal_check' => 15, 'document_completeness_check' => 10,
        ];
        $score = 0;
        foreach ($weights as $key => $weight) {
            if ($checks[$key] ?? false) $score += $weight;
        }
        return $score;
    }
}
```

### 11.2 Verification Code Generator

```php
<?php
// services/VerificationCodeService.php
class VerificationCodeService {

    public function generate(string $applicationId, string $cycleYear): array {
        // Format: GAF-{YEAR}-{6 random alphanumeric, uppercase}
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $code   = "GAF-{$cycleYear}-{$random}";

        // Ensure uniqueness (retry up to 5 times)
        $model = new VerificationCodeModel();
        $attempts = 0;
        while ($model->codeExists($code) && $attempts < 5) {
            $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $code   = "GAF-{$cycleYear}-{$random}";
            $attempts++;
        }

        $expiresAt = (new DateTime())->modify('+72 hours')->format('Y-m-d H:i:s');

        // Generate QR code
        $qrService = new QRCodeService();
        $qrPath    = $qrService->generateForCode($code, $applicationId);

        // Store in DB
        $codeId = $model->create([
            'application_id' => $applicationId,
            'code_value'     => $code,
            'qr_code_path'   => $qrPath,
            'expires_at'     => $expiresAt,
        ]);

        return ['code' => $code, 'qr_path' => $qrPath, 'expires_at' => $expiresAt, 'code_id' => $codeId];
    }

    public function validate(string $code): array {
        $model  = new VerificationCodeModel();
        $record = $model->findByCode($code);
        if (!$record) return ['valid' => false, 'reason' => 'Code does not exist'];
        if ($record['is_used']) return ['valid' => false, 'reason' => 'Code already used'];
        if ($record['invalidated']) return ['valid' => false, 'reason' => 'Code invalidated'];
        if (new DateTime() > new DateTime($record['expires_at'])) {
            return ['valid' => false, 'reason' => 'Code expired'];
        }
        return ['valid' => true, 'applicant_id' => $record['applicant_id'], 'record' => $record];
    }
}
```

---

## 12. NOTIFICATION SYSTEM

### 12.1 Email Service (PHPMailer)

```php
<?php
// services/EmailService.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    private PHPMailer $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = getenv('SMTP_HOST')     ?: 'localhost';
        $this->mailer->Port       = (int)(getenv('SMTP_PORT') ?: 1025); // MailHog dev default
        $this->mailer->SMTPAuth   = (bool)(getenv('SMTP_AUTH') ?: false);
        $this->mailer->Username   = getenv('SMTP_USER')     ?: '';
        $this->mailer->Password   = getenv('SMTP_PASS')     ?: '';
        $this->mailer->SMTPSecure = getenv('SMTP_SECURE')   ?: ''; // 'tls' for production
        $this->mailer->FromName   = 'DMRMS Recruitment Portal';
        $this->mailer->From       = getenv('MAIL_FROM')     ?: 'noreply@dmrms.mil.gh';
        $this->mailer->isHTML(true);
        $this->mailer->CharSet    = 'UTF-8';
    }

    public function send(string $to, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody ?: strip_tags($htmlBody);
            $this->mailer->send();
            $this->log('sent', $to, $subject);
            return true;
        } catch (Exception $e) {
            $this->log('failed', $to, $subject, $e->getMessage());
            return false;
        }
    }

    public function sendTemplate(string $template, string $to, string $toName, array $vars): bool {
        $html = $this->renderTemplate($template, $vars);
        return $this->send($to, $toName, $vars['subject'] ?? 'DMRMS Notification', $html);
    }

    private function renderTemplate(string $template, array $vars): string {
        $templatePath = __DIR__ . "/../views/emails/{$template}.php";
        if (!file_exists($templatePath)) throw new Exception("Email template '{$template}' not found");
        extract($vars);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private function log(string $status, string $to, string $subject, string $error = ''): void {
        $line = date('Y-m-d H:i:s') . " | {$status} | To: {$to} | Subject: {$subject}";
        if ($error) $line .= " | Error: {$error}";
        file_put_contents(__DIR__ . '/../logs/email.log', $line . PHP_EOL, FILE_APPEND);
    }
}
```

### 12.2 SMS Service (Arkesel API)

```php
<?php
// services/SMSService.php
class SMSService {

    private string $apiKey;
    private string $senderId;
    private bool   $enabled;
    private string $baseUrl = 'https://sms.arkesel.com/sms/api';

    public function __construct() {
        $this->apiKey   = getenv('ARKESEL_API_KEY')   ?: '';
        $this->senderId = getenv('ARKESEL_SENDER_ID') ?: 'DMRMS';
        $this->enabled  = !empty($this->apiKey) && filter_var(getenv('SMS_ENABLED'), FILTER_VALIDATE_BOOLEAN);
    }

    public function send(string $phone, string $message): array {
        // Normalize Ghana phone number
        $phone = $this->normalizePhone($phone);

        if (!$this->enabled) {
            // Local fallback: log to DB queue
            return $this->logToQueue($phone, $message);
        }

        $params = [
            'action'   => 'send-sms',
            'api_key'  => $this->apiKey,
            'to'       => $phone,
            'from'     => $this->senderId,
            'sms'      => $message,
        ];

        $ch = curl_init($this->baseUrl . '?' . http_build_query($params));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->log('failed', $phone, $error);
            return ['success' => false, 'error' => $error];
        }

        $data = json_decode($response, true);
        $success = ($data['status'] ?? '') === 'ok';
        $this->log($success ? 'sent' : 'failed', $phone, $data['message'] ?? '');
        return ['success' => $success, 'response' => $data];
    }

    private function normalizePhone(string $phone): string {
        $phone = preg_replace('/\D/', '', $phone);  // Strip non-digits
        if (str_starts_with($phone, '0')) {
            $phone = '233' . substr($phone, 1);     // 0XX → 233XX
        }
        if (!str_starts_with($phone, '233')) {
            $phone = '233' . $phone;
        }
        return $phone;
    }

    private function logToQueue(string $phone, string $message): array {
        // Insert into notifications table with status='pending'
        $db   = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO notifications (recipient_phone, message, channel, status, type)
            VALUES (:phone, :message, 'sms', 'pending', 'system')
        ");
        $stmt->execute([':phone' => $phone, ':message' => $message]);
        $this->log('queued', $phone, 'SMS API disabled — queued for manual sending');
        return ['success' => true, 'queued' => true];
    }

    private function log(string $status, string $phone, string $detail = ''): void {
        $line = date('Y-m-d H:i:s') . " | {$status} | Phone: {$phone} | {$detail}";
        file_put_contents(__DIR__ . '/../logs/sms.log', $line . PHP_EOL, FILE_APPEND);
    }
}
```

### 12.3 Notification Templates

Create email HTML templates in `views/emails/`. Each template is a styled HTML file using the DMRMS color scheme.

**Template list:**
- `registration.php` — Welcome + verify email
- `submission_confirmed.php` — Application received
- `eligibility_pass.php` — Congratulations, eligible
- `eligibility_fail.php` — Not eligible + reasons
- `shortlisted.php` — Shortlisted + verification code
- `appointment_confirmation.php` — Date, time, venue
- `screening_reminder.php` — 24h before screening
- `selected.php` — Selected for recruitment
- `reserve_list.php` — On reserve list
- `rejected.php` — Not selected

**SMS Message Templates:**
```php
// services/SMSTemplates.php
class SMSTemplates {
    public static function registration(string $name): string {
        return "Dear {$name}, welcome to DMRMS. Your account has been created. Login at apply.mil.gh";
    }
    public static function eligibilityPass(string $name): string {
        return "Dear {$name}, congratulations! Your application is ELIGIBLE. Await shortlisting results. DMRMS";
    }
    public static function eligibilityFail(string $name, string $reason): string {
        return "Dear {$name}, your application is INELIGIBLE. Reason: {$reason}. Visit DMRMS portal for details.";
    }
    public static function shortlisted(string $name, string $code): string {
        return "Dear {$name}, you are SHORTLISTED! Your verification code: {$code}. Present at screening. DMRMS";
    }
    public static function appointment(string $name, string $date, string $time, string $venue): string {
        return "Dear {$name}, your screening is on {$date} at {$time}, {$venue}. Bring your verification code. DMRMS";
    }
    public static function selected(string $name): string {
        return "Dear {$name}, CONGRATULATIONS! You have been SELECTED. Await official admission letter. DMRMS";
    }
}
```

### 12.4 Notification Queue Processor

A background process (called by cron or admin trigger) that retries failed notifications:

```php
<?php
// services/NotificationQueueProcessor.php
// Called via: php /path/to/dmrms/services/NotificationQueueProcessor.php
// Or: GET /api/notification/process (Super Admin only)

class NotificationQueueProcessor {
    public function run(): void {
        $db = Database::getInstance();
        $pending = $db->query("
            SELECT * FROM notifications
            WHERE status IN ('pending','retrying')
            AND attempt_count < 3
            ORDER BY created_at ASC
            LIMIT 50
        ")->fetchAll();

        $email = new EmailService();
        $sms   = new SMSService();

        foreach ($pending as $notif) {
            $db->prepare("UPDATE notifications SET attempt_count = attempt_count + 1, last_attempt_at = NOW() WHERE notification_id = :id")
               ->execute([':id' => $notif['notification_id']]);

            $success = false;
            if (in_array($notif['channel'], ['email','both']) && $notif['recipient_email']) {
                $success = $email->send($notif['recipient_email'], '', $notif['subject'] ?? 'DMRMS', $notif['message']);
            }
            if (in_array($notif['channel'], ['sms','both']) && $notif['recipient_phone']) {
                $result  = $sms->send($notif['recipient_phone'], strip_tags($notif['message']));
                $success = $result['success'];
            }

            $status = $success ? 'sent' : ($notif['attempt_count'] >= 3 ? 'failed' : 'retrying');
            $db->prepare("UPDATE notifications SET status = :status, sent_at = CASE WHEN :s = 'sent' THEN NOW() ELSE NULL END WHERE notification_id = :id")
               ->execute([':status' => $status, ':s' => $status, ':id' => $notif['notification_id']]);
        }
    }
}
```

---

## 13. PYTHON MICROSERVICE

### 13.1 Purpose

The Flask microservice (port 5001) handles:
- **Advanced PDF generation** with ReportLab (formal admission letters, detailed reports)
- **QR code badge generation** with PIL (styled verification code badges)
- **Bulk report rendering** (when PHP mPDF is too slow for large datasets)

### 13.2 Flask App (`python_service/app.py`)

```python
# python_service/app.py
from flask import Flask, request, jsonify, send_file
import os
import json
import secrets
from modules.qr_generator import generate_verification_badge
from modules.pdf_reports import generate_admission_letter, generate_bulk_report

app = Flask(__name__)
API_KEY = os.environ.get('PYTHON_SERVICE_KEY', 'dmrms_py_secret_change_me')

def require_api_key(f):
    from functools import wraps
    @wraps(f)
    def decorated(*args, **kwargs):
        key = request.headers.get('X-API-Key')
        if not secrets.compare_digest(key or '', API_KEY):
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'service': 'DMRMS Python Microservice'})

@app.route('/qr/generate', methods=['POST'])
@require_api_key
def generate_qr():
    """
    POST body: { "code": "GAF-2024-A78X3K", "applicant_name": "John Doe",
                 "application_id": "uuid", "output_path": "/path/to/save" }
    """
    data = request.get_json()
    if not data or 'code' not in data:
        return jsonify({'error': 'Missing required fields'}), 400
    try:
        path = generate_verification_badge(
            code           = data['code'],
            applicant_name = data.get('applicant_name', ''),
            application_id = data.get('application_id', ''),
            output_path    = data.get('output_path', '/tmp')
        )
        return jsonify({'success': True, 'path': path})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/pdf/admission-letter', methods=['POST'])
@require_api_key
def admission_letter():
    """Generate styled PDF admission/shortlisting letter."""
    data = request.get_json()
    try:
        pdf_path = generate_admission_letter(data)
        return send_file(pdf_path, as_attachment=True,
                         download_name=f"DMRMS-Letter-{data.get('code','')}.pdf",
                         mimetype='application/pdf')
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/pdf/report', methods=['POST'])
@require_api_key
def bulk_report():
    """Generate large analytics report PDF."""
    data = request.get_json()
    try:
        pdf_path = generate_bulk_report(data)
        return send_file(pdf_path, as_attachment=True,
                         download_name=f"DMRMS-Report-{data.get('cycle','')}.pdf",
                         mimetype='application/pdf')
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5001, debug=False)
```

### 13.3 PHP Bridge to Python Service

```php
<?php
// services/PythonBridgeService.php
class PythonBridgeService {

    private string $baseUrl;
    private string $apiKey;
    private bool   $enabled;

    public function __construct() {
        $this->baseUrl = getenv('PYTHON_SERVICE_URL') ?: 'http://127.0.0.1:5001';
        $this->apiKey  = getenv('PYTHON_SERVICE_KEY') ?: '';
        $this->enabled = !empty($this->apiKey);
    }

    public function generateQR(string $code, string $name, string $applicationId, string $outputPath): ?string {
        if (!$this->enabled) return null;
        $result = $this->post('/qr/generate', compact('code','name','applicationId','outputPath'));
        return $result['path'] ?? null;
    }

    public function generateAdmissionLetter(array $data): ?string {
        if (!$this->enabled) return null;
        return $this->post('/pdf/admission-letter', $data);
    }

    private function post(string $endpoint, array $data): array|null {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "X-API-Key: {$this->apiKey}",
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) return null;
        return json_decode($response, true);
    }
}
```

### 13.4 Python Requirements

```txt
# python_service/requirements.txt
flask==3.0.3
pillow==10.4.0
qrcode[pil]==7.4.2
reportlab==4.2.5
python-dotenv==1.0.1
gunicorn==22.0.0    # for production
```

### 13.5 QR Code Badge Generator

```python
# python_service/modules/qr_generator.py
import qrcode
from PIL import Image, ImageDraw, ImageFont
import os

def generate_verification_badge(code: str, applicant_name: str, application_id: str, output_path: str) -> str:
    """Generates a styled verification code badge with QR code."""
    # Create QR code
    qr = qrcode.QRCode(version=2, error_correction=qrcode.constants.ERROR_CORRECT_H, box_size=8, border=2)
    qr.add_data(f"DMRMS|{code}|{application_id}")
    qr.make(fit=True)
    qr_img = qr.make_image(fill_color="#061948", back_color="white")

    # Create badge canvas
    badge_w, badge_h = 600, 400
    badge = Image.new('RGB', (badge_w, badge_h), color='#061948')
    draw  = ImageDraw.Draw(badge)

    # Gold header bar
    draw.rectangle([0, 0, badge_w, 60], fill='#C8A000')
    # Header text
    draw.text((badge_w//2, 30), "GHANA ARMED FORCES — DMRMS", fill='#061948',
              font=get_font('bold', 20), anchor='mm')

    # Paste QR code
    qr_size = 200
    qr_img  = qr_img.resize((qr_size, qr_size))
    badge.paste(qr_img, (30, 90))

    # Code display
    draw.text((260, 120), "VERIFICATION CODE", fill='#C8A000', font=get_font('bold', 14))
    draw.text((260, 150), code, fill='#FFFFFF', font=get_font('bold', 28))
    draw.text((260, 195), f"Applicant: {applicant_name}", fill='#BEC3D0', font=get_font('regular', 13))
    draw.text((260, 218), f"Ref: {application_id[:8].upper()}", fill='#7A8099', font=get_font('regular', 11))

    # Footer
    draw.rectangle([0, 340, badge_w, 400], fill='#0A2560')
    draw.text((badge_w//2, 360), "Present this code at the screening venue", fill='#BEC3D0',
              font=get_font('regular', 12), anchor='mm')
    draw.text((badge_w//2, 382), "Valid for 72 hours from issue date", fill='#7A8099',
              font=get_font('regular', 10), anchor='mm')

    # Save
    filename = f"qr_{application_id[:8]}_{code.replace('-','_')}.png"
    filepath = os.path.join(output_path, filename)
    os.makedirs(output_path, exist_ok=True)
    badge.save(filepath, 'PNG', dpi=(300, 300))
    return filepath

def get_font(style: str, size: int):
    try:
        fonts = {'bold': 'arial_bold.ttf', 'regular': 'arial.ttf'}
        return ImageFont.truetype(fonts[style], size)
    except:
        return ImageFont.load_default()
```

---

## 14. SECURITY IMPLEMENTATION

### 14.1 .htaccess Security

```apache
# .htaccess — in project root
Options -Indexes -ExecCGI

# Block direct access to sensitive directories
RedirectMatch 403 ^/uploads/.*$
RedirectMatch 403 ^/exports/.*$
RedirectMatch 403 ^/logs/.*$
RedirectMatch 403 ^/config/.*$
RedirectMatch 403 ^/vendor/.*$
RedirectMatch 403 ^/python_service/.*$
RedirectMatch 403 ^/\.env

# URL Rewriting for clean URLs
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/index.php [QSA,L]
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "DENY"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'nonce-{NONCE}'; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; frame-ancestors 'none';"
```

### 14.2 Input Validation

```php
<?php
// core/Validator.php
class Validator {
    private array $errors = [];

    public function required(string $field, $value): self {
        if (empty(trim((string)$value))) {
            $this->errors[$field] = "{$field} is required";
        }
        return $this;
    }

    public function email(string $field, string $value): self {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Invalid email address";
        }
        return $this;
    }

    public function phone(string $field, string $value): self {
        if (!preg_match('/^(\+?233|0)[2345][0-9]{8}$/', $value)) {
            $this->errors[$field] = "Invalid Ghana phone number";
        }
        return $this;
    }

    public function age(string $field, string $dob, int $min, int $max): self {
        $age = (int)(new DateTime())->diff(new DateTime($dob))->y;
        if ($age < $min || $age > $max) {
            $this->errors[$field] = "Age must be between {$min} and {$max} years";
        }
        return $this;
    }

    public function sanitize(string $value): string {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    public function isValid(): bool { return empty($this->errors); }
    public function getErrors(): array { return $this->errors; }
}
```

### 14.3 File Upload Security

```php
<?php
// services/FileUploadService.php
class FileUploadService {

    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ];
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5MB
    private const UPLOAD_BASE_PATH = __DIR__ . '/../uploads/';

    public function handle(array $file, string $docType, string $applicantId): array {
        // 1. Basic upload error check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }

        // 2. Size check
        if ($file['size'] > self::MAX_SIZE_BYTES) {
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }

        // 3. MIME type check via finfo (NOT extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            return ['success' => false, 'error' => 'Invalid file type. Only PDF, JPG, PNG allowed'];
        }

        // 4. Generate UUID filename (prevent path traversal)
        $ext            = self::ALLOWED_MIME_TYPES[$mimeType];
        $storedFilename = bin2hex(random_bytes(16)) . '.' . $ext;

        // 5. Create directory structure
        $dir = self::UPLOAD_BASE_PATH . "{$docType}/{$applicantId}/";
        if (!is_dir($dir)) mkdir($dir, 0750, true);

        // 6. Move file
        $destPath = $dir . $storedFilename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }

        return [
            'success'           => true,
            'original_filename' => basename($file['name']),
            'stored_filename'   => $storedFilename,
            'file_path'         => $destPath,
            'file_size_bytes'   => $file['size'],
            'mime_type'         => $mimeType,
        ];
    }
}
```

### 14.4 CSRF Protection

```php
<?php
// Middleware.php — CSRF token generation
class Middleware {
    public static function generateCSRF(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// In every HTML form:
// <input type="hidden" name="_csrf" value="<?= Middleware::generateCSRF() ?>">
// In JS for AJAX:
// headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
```

### 14.5 Rate Limiting

```php
<?php
// Middleware.php — Login rate limiter
public static function checkLoginRateLimit(string $identifier): bool {
    $key       = 'login_attempts_' . md5($identifier);
    $attempts  = (int)($_SESSION[$key]['count'] ?? 0);
    $lastTime  = $_SESSION[$key]['time'] ?? 0;
    $maxAttempts = (int)(new SystemSettings())->get('max_login_attempts') ?: 5;
    $lockoutSeconds = 900; // 15 minutes

    if ($attempts >= $maxAttempts) {
        if ((time() - $lastTime) < $lockoutSeconds) return false; // Still locked
        unset($_SESSION[$key]); // Reset after lockout period
    }

    $_SESSION[$key] = [
        'count' => $attempts + 1,
        'time'  => time(),
    ];
    return true;
}
```

---

## 15. SUPPORTING MODULES & LIBRARIES

### 15.1 Composer Packages (`composer.json`)

```json
{
  "require": {
    "php":                ">=8.2",
    "phpmailer/phpmailer":  "^6.9",
    "mpdf/mpdf":            "^8.2",
    "endroid/qr-code":      "^5.0",
    "vlucas/phpdotenv":     "^5.6",
    "ramsey/uuid":          "^4.7"
  },
  "autoload": {
    "psr-4": {
      "DMRMS\\": "src/"
    }
  }
}
```

### 15.2 Frontend CDN Libraries (`views/layout/public_header.php`)

```html
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Alpine.js — for reactive UI components (dropdowns, modals, tabs) -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>

<!-- Swiper.js — for image carousels -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- GSAP + ScrollTrigger — for landing page animations -->
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

<!-- Chart.js — for admin analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<!-- Flatpickr — for date/time pickers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Toastify — for toast notifications -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
```

### 15.3 Toast Notification System (`notifications.js`)

```javascript
// public/assets/js/notifications.js
window.Toast = {
  show(type, message, duration = 4000) {
    const colors = {
      success: 'linear-gradient(90deg, #006B3F, #00A360)',
      error:   'linear-gradient(90deg, #CC0000, #FF3333)',
      warning: 'linear-gradient(90deg, #C8A000, #E8B800)',
      info:    'linear-gradient(90deg, #0F3480, #1A4BA0)',
    };
    Toastify({
      text: message,
      duration,
      gravity: 'top',
      position: 'right',
      style: { background: colors[type] || colors.info, borderRadius: '8px' },
    }).showToast();
  },
  success: (msg, d) => Toast.show('success', msg, d),
  error:   (msg, d) => Toast.show('error', msg, d),
  warning: (msg, d) => Toast.show('warning', msg, d),
  info:    (msg, d) => Toast.show('info', msg, d),
};
```

### 15.4 Global AJAX Helper (`app.js`)

```javascript
// public/assets/js/app.js
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

window.API = {
  async request(method, url, data = null) {
    const options = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': CSRF_TOKEN,
        'X-Requested-With': 'XMLHttpRequest',
      },
    };
    if (data && method !== 'GET') options.body = JSON.stringify(data);
    try {
      const res = await fetch(url, options);
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || `HTTP ${res.status}`);
      return json;
    } catch (err) {
      Toast.error(err.message || 'An error occurred');
      throw err;
    }
  },
  get:    (url)        => API.request('GET', url),
  post:   (url, data)  => API.request('POST', url, data),
  put:    (url, data)  => API.request('PUT', url, data),
  delete: (url)        => API.request('DELETE', url),
};

// Loading spinner for async operations
window.Loader = {
  show: () => document.getElementById('global-loader')?.classList.remove('hidden'),
  hide: () => document.getElementById('global-loader')?.classList.add('hidden'),
};

// Confirm modal
window.Confirm = (message) => new Promise(resolve => {
  if (confirm(message)) resolve(true); else resolve(false);
});
```

---

## 16. AI AGENT SKILL INSTRUCTIONS

These are explicit instructions for the AI agent implementing this PRD.

### 16.1 Mandatory Reading Order

Before writing a single line of code, the agent MUST:

1. Read this PRD completely, in order
2. Understand the folder structure in Section 3 — create all directories first
3. Understand the DB schema in Section 4 — run the SQL before writing PHP
4. Understand the design system in Section 5 — define all CSS variables in `main.css` before writing any component CSS
5. Never hardcode colors, fonts, or spacing — always use CSS variables
6. Never hardcode credentials — always use `.env` values via `getenv()`

### 16.2 Build Sequence

Follow this exact sequence to avoid dependency errors:

```
Phase 1 — Infrastructure
  1. Create all directories
  2. Create .env file with all environment variables
  3. Create .htaccess
  4. Create composer.json and run: composer install
  5. Set up PostgreSQL: create database, run full schema SQL
  6. Create config/database.php (PDO singleton)
  7. Create config/session.php, config/constants.php, config/env.php
  8. Create core/Model.php, core/Middleware.php, core/Validator.php, core/Response.php

Phase 2 — Design System
  1. Create public/assets/css/main.css with ALL CSS variables + reset
  2. Create public/assets/css/animations.css with all keyframes
  3. Create views/layout/public_header.php and public_footer.php

Phase 3 — Public Pages
  1. Landing page (index.php + views/public/landing.php + landing.css + landing.js)
  2. Login page (views/public/login.php)
  3. Registration page (views/applicant/register.php)
  4. Eligibility info page
  5. FAQ, Contact, Announcements

Phase 4 — Authentication
  1. AuthController.php (login, logout, forgot-password, reset-password)
  2. Test login for applicant + admin + super_admin

Phase 5 — Applicant Portal
  1. Voucher validation (VoucherController + voucher.php view)
  2. Multi-step registration (applicant register page + JS wizard)
  3. Applicant dashboard
  4. Application form (all 4 sections + auto-save)
  5. Document upload (drag-drop + AJAX upload)
  6. Status timeline
  7. Verification code page

Phase 6 — Eligibility Engine
  1. EligibilityController.php
  2. VerificationCodeService.php
  3. QRCodeService.php (using endroid/qr-code)
  4. Test all 6 criteria with edge cases

Phase 7 — Notification System
  1. EmailService.php + email templates (all 10 templates)
  2. SMSService.php (Arkesel integration + local fallback)
  3. NotificationController.php + queue processor

Phase 8 — Admin Portal
  1. Admin dashboard (KPI cards + Chart.js)
  2. Candidates table (paginated, filterable, sortable)
  3. Candidate detail view (all tabs)
  4. Document review interface
  5. Shortlisting interface
  6. Appointment scheduling
  7. Reports + export (CSV + PDF via mPDF)

Phase 9 — Super Admin Portal
  1. User management
  2. System settings
  3. Audit logs
  4. Backup management

Phase 10 — Python Microservice
  1. Set up Flask app
  2. QR badge generator
  3. PDF admission letter generator
  4. PHP bridge service
  5. Test round-trip (PHP → Flask → response)

Phase 11 — Polish + Responsive
  1. Mobile responsive pass on all pages
  2. All GSAP animations (landing page)
  3. All skeleton loaders
  4. All error states (empty tables, failed uploads)
  5. All success states + confirmations
  6. Loading spinners

Phase 12 — Security Audit
  1. Test SQL injection on every input
  2. Test CSRF on every form
  3. Test file upload bypass attempts
  4. Test RBAC on every admin endpoint
  5. Check all .htaccess blocks work
```

### 16.3 Code Quality Standards

**Every PHP file MUST:**
- Begin with `<?php` (never `<?`)
- Use strict types: `declare(strict_types=1);`
- Use PDO with prepared statements for ALL database queries — no string concatenation in SQL
- Log errors to `/logs/app.log`, never expose to browser
- Sanitize all output with `htmlspecialchars()`
- Return consistent JSON structure: `{'success': bool, 'data': mixed, 'error': string|null}`

**Every JavaScript file MUST:**
- Use `'use strict';` at the top
- Handle all `async/await` with try/catch
- Show loading state before API calls, hide after
- Show error feedback via Toast, never `alert()`
- Never use `innerHTML` with user-supplied data — use `textContent` or `createElement`

**Every CSS file MUST:**
- Use CSS custom properties (variables) for all colors, spacing, typography
- Use logical layout (flexbox/grid), not fixed positioning except for modals/nav
- Include responsive breakpoints: 1200px (desktop), 900px (tablet), 600px (mobile)
- Include `@media (prefers-reduced-motion: reduce)` block

### 16.4 Testing After Each Phase

After completing each phase, the agent MUST verify:
- PHP: No parse errors (`php -l filename.php`)
- Database: All tables created, seed data inserted
- Forms: Submit, validate, and return data correctly
- File uploads: Block invalid types, save valid ones to correct path
- Auth: Each role can access only its pages
- Mobile: Pages are usable on 375px viewport

### 16.5 Default Test Data to Seed

```sql
-- Seed a test recruitment cycle
INSERT INTO recruitment_cycles (cycle_id, cycle_name, start_date, end_date,
    application_open_date, application_close_date, total_vacancies, status)
VALUES (gen_random_uuid(), '2024 General Recruitment', '2024-09-01', '2025-03-01',
    '2024-09-13', '2024-10-11', 500, 'active');

-- Seed test vouchers
INSERT INTO vouchers (voucher_id, cycle_id, serial_number, pin_code, expires_at)
SELECT gen_random_uuid(), c.cycle_id, 'TEST-' || LPAD(s::text, 6, '0'),
    crypt('1234', gen_salt('bf')), '2025-01-01'
FROM recruitment_cycles c, generate_series(1, 20) s
WHERE c.status = 'active';

-- Seed super admin
INSERT INTO administrators (admin_id, first_name, last_name, email, password_hash, role)
VALUES (gen_random_uuid(), 'Super', 'Admin', 'superadmin@dmrms.mil.gh',
    '$2y$12$' || crypt('Admin@2024', gen_salt('bf', 12)), 'super_admin');

-- Seed recruitment admin
INSERT INTO administrators (admin_id, first_name, last_name, email, password_hash, role)
VALUES (gen_random_uuid(), 'Recruitment', 'Officer', 'admin@dmrms.mil.gh',
    '$2y$12$' || crypt('Admin@2024', gen_salt('bf', 12)), 'admin');
```

> **Note to agent:** The `crypt()` function shown is PostgreSQL's — for actual PHP password hashing, use `password_hash('Admin@2024', PASSWORD_BCRYPT, ['cost' => 12])` and store the result. Seed via a PHP CLI script instead.

---

## 17. ENVIRONMENT SETUP GUIDE

### 17.1 `.env` File Template

```ini
# .env — DO NOT COMMIT THIS FILE

# Application
APP_NAME="DMRMS"
APP_ENV="development"
APP_URL="http://localhost/dmrms"
APP_SECRET="change_this_to_a_random_64_char_string"

# Database — PostgreSQL
DB_HOST="localhost"
DB_PORT="5432"
DB_NAME="dmrms_db"
DB_USER="postgres"
DB_PASS="your_postgres_password"

# Email — MailHog for development
SMTP_HOST="127.0.0.1"
SMTP_PORT="1025"
SMTP_AUTH="false"
SMTP_USER=""
SMTP_PASS=""
SMTP_SECURE=""
MAIL_FROM="noreply@dmrms.mil.gh"

# Email — Production (Zoho Mail free tier)
# SMTP_HOST="smtp.zoho.com"
# SMTP_PORT="587"
# SMTP_AUTH="true"
# SMTP_USER="noreply@yourdomain.com"
# SMTP_PASS="your_zoho_app_password"
# SMTP_SECURE="tls"

# SMS — Arkesel (Ghana)
ARKESEL_API_KEY=""
ARKESEL_SENDER_ID="DMRMS"
SMS_ENABLED="false"

# Python Microservice
PYTHON_SERVICE_URL="http://127.0.0.1:5001"
PYTHON_SERVICE_KEY="change_this_python_service_key"

# File Uploads
UPLOAD_MAX_SIZE_MB="5"
UPLOAD_BASE_PATH="C:/xampp/htdocs/dmrms/uploads"
EXPORTS_PATH="C:/xampp/htdocs/dmrms/exports"

# Session
SESSION_LIFETIME_MINUTES="30"
SESSION_NAME="dmrms_session"
```

### 17.2 XAMPP + PostgreSQL Setup Steps

```bash
# 1. Install PostgreSQL 16 to: C:\xampp\pgsql\16
# 2. Add C:\xampp\pgsql\16\bin to Windows PATH
# 3. Start PostgreSQL Windows Service
# 4. Enable PHP PostgreSQL extensions in C:\xampp\php\php.ini:
#    Remove ; from: extension=pdo_pgsql
#    Remove ; from: extension=pgsql
# 5. Restart Apache in XAMPP
# 6. Verify connection:
#    Create test.php in htdocs: <?php phpinfo(); ?>
#    Open http://localhost/test.php — search for 'pdo_pgsql'
# 7. Create database:
#    psql -U postgres -c "CREATE DATABASE dmrms_db ENCODING='UTF8';"
# 8. Run schema:
#    psql -U postgres -d dmrms_db -f C:\xampp\htdocs\dmrms\sql\schema.sql
# 9. Install Composer:
#    Download composer-setup.exe from getcomposer.org
#    Run in htdocs/dmrms: composer install
# 10. Start MailHog for email testing:
#    Download MailHog_windows_amd64.exe
#    Rename to mailhog.exe
#    Run: mailhog.exe
#    Open: http://localhost:8025 to see caught emails
# 11. Start Python microservice:
#    cd C:\xampp\htdocs\dmrms\python_service
#    pip install -r requirements.txt
#    python app.py
```

---

## 18. TESTING CHECKLIST

### 18.1 Functional Tests

**Authentication**
- [ ] Applicant can register with valid voucher
- [ ] Applicant cannot register with used voucher
- [ ] Login works for all three roles
- [ ] 5 failed logins triggers lockout
- [ ] Session expires after 30 minutes
- [ ] Password reset flow works end-to-end

**Application Flow**
- [ ] Form auto-saves every 30 seconds
- [ ] Cannot submit without all required fields
- [ ] Cannot submit without all 4 documents
- [ ] Age calculated correctly from DOB
- [ ] Duplicate application blocked

**Eligibility Engine**
- [ ] 18-year-old passes age check
- [ ] 27-year-old fails age check
- [ ] Non-citizen fails nationality check
- [ ] Missing document fails completeness check
- [ ] All 6 criteria produce correct results
- [ ] Failure reasons logged to `eligibility_results`
- [ ] Application status updated after check
- [ ] Notification queued on completion

**File Upload**
- [ ] PDF uploads successfully
- [ ] JPG uploads successfully
- [ ] .exe rejected with clear error
- [ ] File > 5MB rejected
- [ ] Files stored with UUID names
- [ ] Files not accessible via direct URL

**Notifications**
- [ ] Email sent on registration (caught by MailHog)
- [ ] SMS logged to queue when no API key
- [ ] Failed notification retried by queue processor
- [ ] All 10 email templates render correctly

**Admin Operations**
- [ ] Candidate table paginates correctly
- [ ] Status filter works
- [ ] Bulk shortlist updates all selected candidates
- [ ] Verification codes auto-generated on shortlist
- [ ] QR code image generated and stored
- [ ] Appointment slot capacity enforced
- [ ] CSV export contains all candidate data
- [ ] PDF report generates with correct GAF branding

**Security**
- [ ] SQL injection attempt rejected on all inputs
- [ ] CSRF token mismatch returns 403
- [ ] Applicant cannot access admin routes
- [ ] Admin cannot access super admin routes
- [ ] `/uploads/` returns 403 when accessed directly
- [ ] Password stored as bcrypt hash (cost 12)

---

## 19. DEPLOYMENT MIGRATION PATH

When ready to move from XAMPP to production:

### 19.1 Server Requirements
- Ubuntu 22.04 LTS
- PHP 8.2 (Apache or Nginx)
- PostgreSQL 16
- Python 3.11+
- SSL/TLS certificate (Let's Encrypt)

### 19.2 Migration Steps

1. **Database:** `pg_dump -U postgres dmrms_db > dmrms_backup.sql` on Windows, `psql -U postgres dmrms_db < dmrms_backup.sql` on server
2. **Files:** Rsync project files (exclude `.env`, `vendor/`, `uploads/`, `logs/`)
3. **Config:** Update `.env` with production values (SMTP → Zoho, SMS → Arkesel with real key)
4. **HTTPS:** Install SSL, update `.htaccess` to force HTTPS redirect
5. **Python service:** Run with Gunicorn as a systemd service (port 5001, localhost only)
6. **Cron jobs:** Schedule notification queue processor every 5 minutes

```bash
# Crontab entry for notification queue
*/5 * * * * php /var/www/dmrms/services/NotificationQueueProcessor.php >> /var/www/dmrms/logs/cron.log 2>&1
```

### 19.3 SMS Production (Arkesel)

1. Register at arkesel.com
2. Top up credit (pay-as-you-go, ~GHS 0.05–0.10/SMS)
3. Register sender ID "DMRMS" (2–3 business days approval)
4. Set `ARKESEL_API_KEY` in production `.env`
5. Set `SMS_ENABLED=true`

### 19.4 CDN Fallback Strategy

If CDN scripts fail (offline environment), the `public/assets/js/` folder should contain local copies of:
- GSAP + ScrollTrigger
- Alpine.js
- Chart.js
- Swiper.js
- Flatpickr

All `<script>` tags should use `onerror` fallback to local copies:
```html
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"
        onerror="this.src='/assets/js/vendor/gsap.min.js'"></script>
```

---

## APPENDIX A — GAF BRANDING REFERENCE

| Element | Value |
|---|---|
| Primary site URL | gafonline.mil.gh |
| Recruitment portal | apply.mil.gh |
| Color from live site | `#061948` (meta theme-color) |
| Logo style | White on dark navy |
| Typography style | Bold, condensed for headings |
| Social: Facebook | GhArmedForcesOfficial |
| Social: Twitter/X | GhArmedForces |
| Social: Instagram | gharmedforcesofficial |
| Social: YouTube | officialgaftv |
| Contact phone | (+233) 509 215 775 |
| Address | Burma Camp, Accra |

---

## APPENDIX B — GHANA-SPECIFIC REQUIREMENTS

| Requirement | Detail |
|---|---|
| Citizenship check | Ghanaian by birth |
| Minimum education | WASSCE: 6 credits including English |
| Age range | 18–26 years (varies by cycle) |
| Height (Male) | ≥ 1.65m (general) / ≥ 1.75m (Military Police) |
| Height (Female) | ≥ 1.58m (general) / ≥ 1.70m (Military Police) |
| Voucher price | GHC 350.00 |
| Distribution | Ghana Post offices nationwide |
| SMS gateway | Arkesel / mNotify / Hubtel (Ghana-based) |
| Phone format | +233 prefix, 10 digits total |
| Regions | 16 administrative regions |
| Service branches | Ghana Army / Ghana Navy / Ghana Air Force |
| Application portal | apply.mil.gh (existing, this replaces/supplements) |

---

*End of DMRMS Product Requirements Support Document*  
