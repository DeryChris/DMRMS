# Digital Military Recruitment Management System (DMRMS)

## Complete Product Requirements Document (PRD)

**Version**: 4.0 Enterprise Edition
**Date**: June 2026
**Classification**: Confidential – Development Use Only
**Target Environment**: Local Hosting (Windows + XAMPP)
**AI Service**: OpenAI API with Modular Fallback


---

## Executive Summary

The Digital Military Recruitment Management System (DMRMS) is a comprehensive, AI-powered, end-to-end web-based platform designed to transform the Ghana Armed Forces recruitment process from a manual, paper-intensive workflow into a fully digital, automated, and intelligent system. This system serves as the digital backbone for managing the entire recruitment lifecycle—from recruitment cycle creation and voucher purchase through application submission, automated eligibility screening, shortlisting, appointment scheduling, physical screening, final selection, and notification.

The system addresses critical challenges identified in the current recruitment process: slow application processing, certificate forgery risks, inconsistent eligibility assessments, overcrowding at recruitment centres, difficulty in tracking applicant records, and lack of real-time recruitment analytics.

**Core Value Proposition:**
- **End-to-End Digital Recruitment** – Every stage of the recruitment process is digitized and automated
- **AI-Powered Intelligence** – OpenAI integration provides intelligent document analysis, fraud detection, candidate ranking, and applicant assistance
- **Production-Ready Architecture** – Designed for local XAMPP deployment with clear paths to production scaling
- **Premium Subscription Model** – Free core functionality with optional premium AI features
- **Security & Compliance** – Built with military-grade security and Ghana Data Protection Act compliance

---

## 1. Project Overview

### 1.1 Project Name
Digital Military Recruitment & Management System (DMRMS)

### 1.2 Project Type
Enterprise Web-Based Recruitment Management Platform with AI Integration

### 1.3 Deployment Targets
- Localhost (XAMPP) – Primary development and testing
- Institutional Server – On-premise deployment for GAF
- VPS – Cloud-based production deployment
- Future Government Infrastructure – Scalable to national level

### 1.4 Primary Objective
Develop a complete military recruitment management platform that digitizes, automates, secures, tracks, and manages the entire recruitment lifecycle from recruitment cycle creation through final admission decisions.

### 1.5 Key Features
- **Public Portal** – Recruitment announcements, eligibility checker, and news feed
- **Voucher-based Registration** – Unique serial/pin validation system
- **Multi-step Application Form** – Comprehensive data collection with document upload
- **Automated Eligibility Engine** – Rule-based and AI-enhanced eligibility verification
- **AI-Powered Intelligence Layer** – Document analysis, fraud detection, candidate ranking, chatbot
- **Shortlisting & Verification Code Generation** – Unique QR codes for screening entry
- **Appointment Scheduling** – Automated slot allocation with notifications
- **Screening Management** – Medical, fitness, and interview results recording
- **Final Selection** – Committee decision workflow (admitted/deferred/rejected)
- **Administrative Dashboard** – KPI cards, real-time charts, exportable reports
- **Premium Subscription Model** – Free core with paid AI enhancements
- **Comprehensive Auditing** – Complete audit trail for all system actions

---

## 2. Technology Stack

### 2.1 Primary Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Frontend** | HTML5, CSS3 (Tailwind CSS), JavaScript (ES6+), Alpine.js, Chart.js | Responsive UI, animations, charts |
| **Backend** | PHP 8.2+ (Laravel 11/12) | MVC framework, routing, ORM, authentication |
| **Database** | MySQL 8.0 / MariaDB 10.4+ (or PostgreSQL 15+) | Relational data storage |
| **Local Server** | XAMPP (Apache + PHP + MySQL) | Local hosting environment |
| **AI Services** | OpenAI API (GPT-4, Embeddings, Vision) + Python FastAPI | Natural language processing, document analysis, ranking |
| **Queue Worker** | Laravel Queue (database driver) | Background jobs (eligibility, notifications) |
| **Scheduler** | Laravel Scheduler (cron) | Periodic tasks (report generation, AI batch processing) |
| **Supplemental** | Python 3.9+ (for AI orchestration, document preprocessing) | Integration with OpenAI, image processing |
| **Storage** | Local disk (`storage/app/public`) for documents | Document storage (S3 fallback optional) |

### 2.2 Why Laravel (PHP) Over Node.js/React

| Consideration | Laravel (PHP) | Node.js/React |
|---------------|---------------|---------------|
| **Learning Curve** | Lower; well-documented | Steeper; requires understanding of JS ecosystem |
| **Government Compliance** | Proven track record in government systems | Less established in this context |
| **Security** | Built-in CSRF, XSS, SQL injection protection | Requires additional middleware |
| **Maintenance** | Simpler deployment; single codebase | Requires separate frontend/backend builds |
| **Performance** | Sufficient for this use case | Higher concurrency but unnecessary complexity |
| **Hosting** | Widely available, cost-effective | Requires Node-specific hosting |

### 2.3 Setup Instructions

1. Install [PostgreSQL](https://www.postgresql.org/) 15+ with pgAdmin
2. Install [XAMPP](https://www.apachefriends.org/) with PHP 8.2+ (Apache + PHP only)
3. Install Composer globally
4. Create a new Laravel project: `composer create-project laravel/laravel dmrms`
5. Configure `.env` to use PostgreSQL database
6. Enable required PHP extensions: `pdo_pgsql`, `gd`, `fileinfo`, `zip`, `curl`, `mbstring`
7. Install Python 3.9+ and required packages: `pip install openai pillow requests fastapi uvicorn`
8. Run `php artisan serve` or access via Apache virtual host

---

## 3. User Roles & Permissions

### 3.1 Role Definitions

| Role | Description | Access Level |
|------|-------------|--------------|
| **Public Visitor** | Unauthenticated website visitor | View only |
| **Applicant** | Registered applicant | Personal data only |
| **Screening Officer** | On-site verification personnel | Limited operational |
| **Medical Officer** | Medical assessment personnel | Limited operational |
| **Interview Officer** | Interview assessment personnel | Limited operational |
| **Recruitment Administrator** | Day-to-day recruitment management | Full operational |
| **Super Administrator** | System-wide configuration | Full system access |

### 3.2 Role-Specific Permissions

#### Public Visitor
- View recruitment campaigns
- View eligibility requirements
- Purchase vouchers
- Read FAQs
- Contact support

#### Applicant
- Register and login
- Complete applications
- Upload documents
- Track application status
- Receive notifications
- View appointments
- Download admission letters

#### Screening Officer
- Verify applicants via QR codes
- Record attendance
- Validate verification codes

#### Medical Officer
- Record medical examination results
- Approve medical fitness
- Enter medical notes

#### Interview Officer
- Record interview scores
- Submit recommendations
- Enter interview notes

#### Recruitment Administrator
- Manage applicants and applications
- Manage shortlisting
- Manage appointments
- Generate reports
- Verify documents
- Schedule screening sessions

#### Super Administrator
- Configure system
- Manage all users
- View audit logs
- Manage recruitment cycles
- Configure security settings
- Manage AI subscriptions
- View AI usage analytics

---

## 4. System Architecture

### 4.1 Three-Tier Architecture with AI Layer

```
┌──────────────────────────────────────────────────────────────────┐
│                    Presentation Layer                            │
│  HTML/CSS/JS + Alpine.js (Public, Applicant, Admin)              │
│  • Public Portal    • Applicant Dashboard    • Admin Panel       │
└────────────────────────────┬─────────────────────────────────────┘
                             │ REST API / Web Routes
┌────────────────────────────▼─────────────────────────────────────┐
│                    Application Logic (Laravel)                   │
│  Controllers, Services, Middleware, Validation                   │
│  ┌─────────────────────────────────────────────────────┐        │
│  │              AI Gateway Service Layer               │        │
│  │  • Applicant Assistant    • Eligibility Advisor     │        │
│  │  • Document Analysis      • Fraud Detection         │        │
│  │  • Smart Shortlisting     • Report Generation       │        │
│  │  • Analytics Intelligence • Audit Assistant         │        │
│  └──────────────┬──────────────────────────────────────┘        │
│                 │                                                │
│                 ▼ (Calls Python FastAPI microservices)          │
└──────────────────────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼─────────────────────────────────────┐
│                    Data Layer (MySQL/PostgreSQL)                 │
│  • Applicant Data    • Application Data    • Document Metadata   │
│  • Eligibility Data  • Appointment Data    • Audit Logs          │
└──────────────────────────────────────────────────────────────────┘
```

### 4.2 Modular Structure

#### Core Modules (Free)
1. Authentication (voucher, email/phone verification)
2. Application & Document Management
3. Rule-Based Eligibility Engine
4. Shortlisting & Verification Code
5. Appointment Scheduling
6. Screening & Final Decision
7. Basic Reporting (PDF/Excel export)
8. Admin Dashboard (KPI cards, charts)

#### Premium Modules (Subscription)
1. Advanced Analytics Dashboard (drill-down charts, predictive insights)
2. Bulk SMS/Email Marketing
3. Real-time ID Verification (Ghana Card API)
4. Automated Certificate Verification (WASSCE scratch card validation)
5. AI-based Candidate Ranking
6. Document Fraud Detection
7. Intelligent Applicant Chatbot
8. Predictive Analytics
9. AI Report Generation (natural language)

#### Fallback Implementations
- Use local SMTP (mailtrap.io) instead of paid email providers
- Use email fallback instead of SMS
- Use Chart.js for static analytics instead of premium BI tools
- Rule-based eligibility when AI unavailable

### 4.3 File Structure

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

---

## 5. AI-Powered Intelligence Layer

### 5.1 AI Strategy

**Primary AI Provider:** OpenAI API

**Models Used:**
- GPT-4 / GPT-4 Turbo – Natural language understanding and generation
- GPT-4 Vision – Document image analysis
- text-embedding-3-small – Candidate profiling and ranking
- Assistants API – Applicant support chatbot

**Design Principle:** The platform must be designed so AI providers can be replaced later without requiring major system redesign.

**Future Providers:** Azure OpenAI, Anthropic Claude, Google Gemini, Local LLMs (Llama 3 via Ollama)

### 5.2 AI Gateway Architecture

```
Frontend Application
        ↓
Laravel Backend Controllers
        ↓
AI Gateway Service (Laravel)
        ↓
┌───────────────────────────────────┐
│  AI Provider Abstraction Layer    │
│  ┌─────────────────────────────┐ │
│  │  OpenAI Provider            │ │
│  │  - Completion API           │ │
│  │  - Vision API               │ │
│  │  - Embedding API            │ │
│  │  - Assistants API           │ │
│  └─────────────────────────────┘ │
│  ┌─────────────────────────────┐ │
│  │  Fallback Provider          │ │
│  │  - Rule-based logic         │ │
│  │  - Mock responses           │ │
│  └─────────────────────────────┘ │
└───────────────────────────────────┘
        ↓
Response Processing Layer
        ↓
Database + User Interface
```

### 5.3 AI Modules

#### Module A: AI Applicant Assistant (Chatbot)
**Purpose:** Act as a military recruitment virtual assistant

**Availability:**
- Homepage (public)
- Applicant Portal (authenticated)
- Mobile responsive

**Capabilities:**
- Explain recruitment process
- Explain eligibility rules
- Explain application requirements
- Explain document requirements
- Explain recruitment stages
- Explain appointment procedures
- Answer FAQs with contextual understanding

**Examples:**
- "What documents do I need to apply?"
- "Am I eligible with my qualifications?"
- "What happens after shortlisting?"
- "When is my appointment date?"

**Technical Implementation:**
- Uses OpenAI Assistants API
- Custom system prompt with GAF recruitment rules
- Knowledge base of recruitment policies
- Session-based thread management

---

#### Module B: AI Eligibility Advisor
**Purpose:** Provide pre-submission eligibility guidance

**Capabilities:**
- Review applicant data (age, education, height, gender, nationality)
- Generate eligibility confidence score
- Provide specific improvement recommendations
- Explain failure reasons in plain language

**Results:**
- High Probability – Likely to qualify
- Medium Probability – Meets some criteria
- Low Probability – Unlikely to qualify
- Not Qualified – Clearly ineligible

**Implementation:**
- Uses GPT-4 with structured prompts
- Compares applicant data against cycle requirements
- Generates advisory only – final decisions from system rules
- Confidence score displayed with explanation

---

#### Module C: AI Document Analysis
**Purpose:** Automatically process and extract information from uploaded documents

**Supported Documents:**
- Birth Certificates
- Ghana Card (National ID)
- Passport
- Educational Certificates (WASSCE, SSCE, Degree)
- Medical Records (if applicable)

**Analysis Performed:**
- Document quality assessment (readability, completeness)
- Text extraction using OpenAI Vision
- Data extraction (names, dates, certificate numbers)
- Comparison with application data
- Mismatch flagging

**Output:**
- Extracted text
- Key-value pairs
- Confidence scores
- Mismatch alerts

**Fallback:** OCR with Tesseract when OpenAI unavailable

---

#### Module D: AI Fraud Detection Engine
**Purpose:** Detect suspicious applications and prevent fraud

**Checks Performed:**
- Duplicate Names (same name, different applications)
- Duplicate Certificates (same certificate number)
- Duplicate Photos (same photo across applications)
- Duplicate Phone Numbers
- Duplicate National IDs
- Repeated Voucher Usage
- Inconsistent Personal Information
- Potential Fake Documents (visual artifacts)

**Risk Scoring:**
| Score Range | Risk Level | Action |
|-------------|------------|--------|
| 0-25 | Low | No action |
| 26-50 | Medium | Flag for review |
| 51-75 | High | Automatic review |
| 76-100 | Critical | Immediate admin notification |

**Implementation:**
- Batch processing via Laravel queue
- Uses OpenAI Vision for image analysis
- Embedding comparison for duplicate detection
- Automated flagging workflow

---

#### Module E: AI Smart Shortlisting Assistant
**Purpose:** Support administrators in candidate selection

**Capabilities:**
- Candidate ranking based on qualifications
- Vacancy matching analysis
- Regional distribution analysis
- Gender distribution analysis
- Recruitment trend analysis

**Ranking Methodology:**
1. Convert candidate profiles to embeddings (OpenAI text-embedding-3-small)
2. Compare against ideal candidate profile (cycle requirements)
3. Compute similarity scores
4. Generate ranked list with explanations

**Important:** AI does NOT make final decisions. Administrators review and approve all shortlists.

**Fallback:** Simple keyword-based scoring when AI unavailable

---

#### Module F: AI Report Generation
**Purpose:** Generate recruitment reports using natural language

**Capabilities:**
- Accept natural language requests
- Generate executive summaries
- Create charts and visualizations
- Provide insights and recommendations

**Examples:**
- "Generate regional recruitment report for Ashanti Region"
- "Show applicant trends for the last 30 days"
- "Compare gender distribution across regions"
- "Identify recruitment bottlenecks in the process"

**Export Formats:**
- PDF (formatted report)
- Excel (data tables)
- Word (document format)

---

#### Module G: AI Analytics Center
**Purpose:** Continuous analysis of recruitment data

**Insights Provided:**
- Application growth trends
- Regional demand analysis
- Drop-off analysis (where applicants abandon the process)
- Screening attendance trends
- Gender trends over time
- Qualification trends
- Recruitment bottlenecks identification

**Dashboard Components:**
- AI Insights Panel – Natural language summaries
- AI Recommendations Panel – Actionable suggestions
- Recruitment Health Score – Composite metric
- Trend Forecasting – Volume and success predictions

---

#### Module H: AI Audit Assistant
**Purpose:** Support Super Administrators in system auditing

**Capabilities:**
- Analyze user activity patterns
- Identify suspicious behavior
- Detect unusual access patterns
- Detect mass data exports
- Detect privilege abuse
- Generate audit summaries
- Generate compliance reports

**Implementation:**
- Uses GPT-4 to analyze audit logs
- Pattern recognition and anomaly detection
- Natural language summaries
- Compliance checklists

---

#### Module I: AI Recruitment Command Center
**Purpose:** Executive-level recruitment oversight

**Dashboard Components:**
- Recruitment Health Score – Overall process health (0-100)
- Applicant Flow Analysis – Pipeline visualization
- Regional Recruitment Map – Geographic distribution
- Risk Indicators – Red flags and warnings
- AI Recommendations – Actionable suggestions
- Forecast Models – Volume and success predictions
- Recruitment Performance Trends – Historical comparison

---

### 5.4 OpenAI Cost Control

**Critical Requirement:** The system must include comprehensive cost management

**Usage Tracking:**
- Per user usage tracking
- Per department usage tracking
- Daily cost tracking
- Monthly cost tracking
- Token monitoring (input/output per request)

**Rate Limiting:**
- Requests per minute per user
- Requests per minute per IP
- Total tokens per day

**Budget Controls:**
- Budget threshold alerts (email notification)
- Automatic AI suspension rules
- Usage quota enforcement

**Cost Visibility Dashboard:**
- Daily/Monthly cost chart
- Most expensive operations
- User-level cost breakdown
- Projected costs

---

### 5.5 AI Security Requirements

**Data Protection:**
- No applicant data may be exposed publicly
- Sensitive fields must be masked before sending to OpenAI
- Use OpenAI's zero-retention option where possible
- All documents anonymized (remove PII)

**Audit Logs:**
- AI Request Logs (prompt, model, tokens used)
- AI Response Logs (response, processing time)
- Usage Logs (user, cost)
- Error Logs (failures, fallback triggers)

**Compliance:**
- GDPR/DPA compliance through data minimization
- Consent management for AI processing
- Right to explanation for AI decisions

---

### 5.6 AI Prompt Management

**Central Prompt Repository:**

| Table | Purpose |
|-------|---------|
| `ai_prompts` | Store prompt templates |
| `ai_prompt_versions` | Version control for prompts |
| `ai_prompt_logs` | Audit of prompt usage |

**Benefits:**
- Version control for all prompts
- Prompt testing and optimization
- Auditability of AI interactions
- Easy prompt updates without code changes

---

## 6. Functional Requirements

### 6.1 Public Portal (Unauthenticated)

#### FR-PUB-01: Recruitment Cycle Display
- Display active recruitment cycles with key details
- Show countdown timer to cycle deadline
- Display past cycles with archived status

#### FR-PUB-02: Eligibility Pre-Checker
- Form with basic criteria (age, nationality, education level)
- Instant pass/fail feedback with clear reasons
- No data storage; purely informational

#### FR-PUB-03: Voucher Purchase Information
- Display scratch card/e-voucher purchase locations
- Provide step-by-step purchase instructions
- Cost: GH₵50–200 depending on category

#### FR-PUB-04: Application Guide
- Detailed step-by-step application guide
- Document requirements list with specifications
- Browser compatibility information

#### FR-PUB-05: Announcement Feed
- Scrollable list of recruitment announcements
- Category filtering (General, Requirements, Deadlines, Results)

#### FR-PUB-06: AI Chatbot (Premium)
- Floating widget on public pages
- Answer FAQ about recruitment process
- Guide visitors through eligibility checking
- Provide document requirement information

### 6.2 Applicant Registration & Authentication

#### FR-REG-01: Account Creation
- Applicant enters voucher code (serial number + PIN)
- System validates voucher against database (valid, unused, within cycle)
- Applicant provides: Full name, date of birth, gender, contact number, email, residential address, region
- System sends verification code to email and phone
- Applicant sets secure password (min 8 chars, mix of uppercase, lowercase, numbers, special)
- Account activated upon successful verification
- **Output**: Activated applicant account linked to voucher

#### FR-REG-02: Login
- Email/username + password authentication
- JWT-based session management (Laravel Sanctum)
- "Remember me" functionality
- Failed attempt limiting (5 attempts → 15-minute lockout)
- Password reset via email

#### FR-REG-03: Profile Management
- View and edit profile information
- Change password with current password verification
- View application history

### 6.3 Application Submission

#### FR-APP-01: Personal Information
- Full name, date of birth (age calculated automatically)
- Gender, marital status
- Contact details: Phone, email, residential address
- Region and district of origin
- Nationality (must be Ghanaian by birth)
- National ID (Ghana Card) number

#### FR-APP-02: Educational Information
- **For Regular Enlistment**: Minimum 6 credits in WASSCE including English, Mathematics, and Science
- **For Tradesmen**: Technical/Vocational Certificate with relevant experience
- **For Officer Cadets**: Bachelor's degree or higher from recognized university
- Fields: Institution name, qualification, year obtained, certificate number
- WASSCE/SSCE Index Number and results slip serial number
- Upload certificate (JPEG, max 2MB)

#### FR-APP-03: Physical & Health Information
- Height (meters) – Male: ≥1.65m (or 1.68m per some sources)
- Weight (kg)
- Medical conditions declaration (checkbox list)
- Criminal record declaration (must be free of criminal record)
- Fitness status self-assessment

#### FR-APP-04: Document Upload
**Required Documents:**
1. **Birth Certificate** – Official birth certificate confirming date of birth and nationality
2. **Educational Certificate** – Certified copies of academic qualifications
3. **National ID** – Valid government-issued identification (Ghana Card/Passport)
4. **Passport Photograph** – Recent, 200×180 pixels (or 170×150), JPEG format
5. **WASSCE/SSCE Certificate** – Scanned copy
6. **Degree Certificate** (if applicable) – Scanned copy

**Validation Rules:**
- File type: JPEG, PNG, PDF
- File size: ≤2MB per document
- All required documents must be present before submission

**AI Enhancement (Premium):**
- Automatic text extraction from documents
- Comparison with entered data
- Mismatch flagging
- Fraud risk scoring

#### FR-APP-05: Save Draft, Review & Submit
- Auto-save functionality (every 30 seconds)
- Draft saved locally and on server
- Review page showing all entered data
- Edit capability before final submission
- Final submission with confirmation checkbox
- **Output**: Submitted application with unique GAF ID

#### FR-APP-06: AI Eligibility Advisor (Premium)
- Pre-submission eligibility confidence score
- Specific recommendations
- Explanation of potential issues
- Guided improvement suggestions

### 6.4 Automated Eligibility Screening

#### FR-ELIG-01: Eligibility Criteria Evaluation
The system evaluates each applicant against the following criteria:

| Criterion | Requirement | System Check |
|-----------|-------------|--------------|
| **Age** | 18–25 years (Regular), 18–27 years (Tradesmen), 18–30 years (Officer) | Calculated from date of birth |
| **Nationality** | Ghanaian citizen by birth | Validated against ID document |
| **Education** | Minimum WASSCE/BECE/Technical qualification | Matched against submitted certificates |
| **Height** | ≥1.65m (Male), ≥1.58m (Female) | Entered in application form |
| **Marital Status** | Not married (Regular enlistment) | Declaration in form |
| **Criminal Record** | No prior convictions | Declaration in form |
| **Document Completeness** | All required documents submitted | Document upload checklist |
| **Medical Fitness** | Medically fit by GAF standards | Declaration and eventual physical screening |

#### FR-ELIG-02: Eligibility Decision
- **Eligible**: Applicant meets all criteria; automatically advanced to shortlisting pool
- **Not Eligible**: Applicant notified of specific failed criteria; application flagged

#### FR-ELIG-03: AI Eligibility Enhancement (Premium)
- AI document verification (extract and compare)
- Fraud detection scoring
- Confidence score with explanation
- Human-readable summary

#### FR-ELIG-04: Verification Code Generation
- Upon eligibility confirmation, system generates unique verification code
- Code is time-bound, single-use, stored securely
- Code sent via SMS and email to applicant
- Code used for physical screening entry and appointment verification

### 6.5 Shortlisting & Administrator Review

#### FR-SHORT-01: Administrator Review
- View filtered list of eligible applicants
- Review individual applicant profiles and eligibility results
- Approve or defer candidates based on vacancy availability
- Batch or individual shortlist notifications

#### FR-SHORT-02: Shortlist Management
- Real-time monitoring of shortlist pool against available vacancies
- Export shortlist to PDF/Excel
- Generate shortlist reports

#### FR-SHORT-03: AI Smart Shortlisting (Premium)
- AI-ranked candidate list with explanations
- Regional and gender distribution analysis
- Vacancy matching recommendations
- Trend analysis and insights

### 6.6 Appointment Scheduling

#### FR-SCHED-01: Slot Configuration
- Administrator defines: Date, time slots, venue, capacity per slot
- System automatically allocates slots to shortlisted applicants
- Conflict detection and resolution

#### FR-SCHED-02: Applicant Notification
- Automated notification of appointment details (date, time, venue)
- SMS and email delivery
- Calendar invite (ICS file) option

#### FR-SCHED-03: Appointment Management
- View all scheduled appointments
- Reschedule functionality (with notification)
- Check-in/attendance tracking
- Generate attendance reports

### 6.7 Screening Management

#### FR-SCR-01: Entry Verification
- Verification code scanned/entered at screening venue
- System validates code against database (valid, unused, matches applicant)
- Entry granted only upon successful validation

#### FR-SCR-02: Screening Data Entry
- Medical Examination: Health assessment results recorded
- Fitness Assessment: Physical fitness test scores recorded
- Interview Assessment: Communication and suitability evaluation
- All results linked to applicant profile

#### FR-SCR-03: Results Recording
- All assessment outcomes entered into system
- Status updates: Pass/Fail for each stage
- Final screening result recorded

### 6.8 Final Selection

#### FR-SEL-01: Selection Committee Review
- Aggregated view of all screening data
- Committee deliberation support
- Three possible outcomes: Admitted, Deferred, Rejected

#### FR-SEL-02: Decision Recording
- Final decision formally recorded in system
- Decision maker and timestamp logged
- Audit trail maintained

### 6.9 Notification System

#### FR-NOT-01: Multi-channel Notifications
Notifications dispatched across seven key recruitment milestones:
1. Account activation
2. Application submission confirmation
3. Eligibility result
4. Shortlisting notification with verification code
5. Appointment scheduling
6. Screening reminder
7. Final selection decision

#### FR-NOT-02: Delivery Methods
- **Email**: HTML templates with branding
- **SMS**: Concise, mobile-optimized messages
- **Dashboard**: In-app notifications with read/unread status

### 6.10 Reporting & Analytics

#### FR-REP-01: Key Performance Indicators
| KPI | Description |
|-----|-------------|
| **Total Applicants** | Complete count of all registered and submitted applications per cycle |
| **Eligible Applicants** | Number who passed eligibility verification |
| **Rejected Applicants** | Count who did not meet criteria at any stage |
| **Regional Distribution** | Breakdown by geographic region for equity analysis |
| **Gender Distribution** | Proportional representation by gender |
| **Success Rate** | Percentage who completed the full recruitment journey |
| **Screening Pass Rate** | Percentage who passed physical screening |
| **AI Processing Rate** | Percentage of applications processed with AI (Premium) |
| **Fraud Risk Distribution** | Risk score distribution (Premium) |
| **Application Bottlenecks** | Stage where most applicants drop off (Premium) |

#### FR-REP-02: Export Capabilities
- PDF reports (formatted, print-ready)
- Excel/CSV exports for further analysis
- Custom report builder (date range, filters)

#### FR-REP-03: AI Report Generation (Premium)
- Natural language report requests
- Executive summaries
- Insight generation
- Recommendation suggestions

### 6.11 Administrative Management

#### FR-ADMIN-01: Recruitment Cycle Management
- Create, edit, publish, and archive recruitment cycles
- Configure: Name, start date, end date, total vacancies, requirements
- Set eligibility criteria parameters
- Configure AI settings per cycle

#### FR-ADMIN-02: User Management
- Create administrator accounts
- Assign roles: Recruitment Admin, Super Admin
- Suspend/activate accounts
- View audit logs

#### FR-ADMIN-03: Document Management
- View all uploaded documents
- Verify/flag documents
- Document retention and purging policies

#### FR-ADMIN-04: AI Configuration (Super Admin)
- Enable/disable AI features
- Configure AI provider settings
- Set usage limits and budgets
- Monitor AI usage and costs
- View AI audit logs

---

## 7. Non-Functional Requirements

### 7.1 Security Requirements

| Requirement | Implementation |
|-------------|----------------|
| **Authentication** | PHP/Laravel built-in authentication with bcrypt hashing |
| **Session Management** | Secure, HTTP-only cookies with configurable expiry |
| **Authorization** | Role-Based Access Control (RBAC) with middleware |
| **Data Encryption** | TLS 1.3 for all transmission; AES-256 for stored documents |
| **Input Validation** | Server-side validation; parameterized queries for SQL injection prevention |
| **CSRF Protection** | Laravel's built-in CSRF tokens |
| **XSS Prevention** | Output escaping; Content Security Policy headers |
| **Rate Limiting** | 60 requests/minute for sensitive endpoints |
| **Audit Logging** | All critical actions logged with user, timestamp, IP |
| **AI Data Protection** | Anonymization before sending to AI; zero-retention policy |

### 7.2 Performance Requirements

| Metric | Target |
|--------|--------|
| Page load time | < 2 seconds |
| API response time | < 500ms (95th percentile) |
| AI processing time | < 5 seconds (async for batch) |
| Concurrent users | 10,000+ |
| Database connections | Pooled, max 100 concurrent |
| File upload speed | < 5 seconds for 2MB files |
| Notification delivery | < 30 seconds |

### 7.3 Scalability Requirements
- Horizontal scaling: Multiple application servers behind load balancer
- Database: Read replicas for reporting queries
- Caching: Redis for session storage and frequent queries
- AI: Queued processing for batch operations
- CDN: Static assets served via CDN during peak traffic

### 7.4 Availability Requirements
- 99.9% uptime during active recruitment periods
- Scheduled maintenance windows communicated 48 hours in advance
- Automatic failover for critical services
- AI service degraded mode when API unavailable

### 7.5 Usability Requirements
- WCAG 2.1 AA compliance
- Mobile-first responsive design
- Support for Chrome, Firefox, Edge, Safari
- Maximum 3 clicks to complete any primary task
- Form completion time: < 20 minutes for average user

### 7.6 Maintainability Requirements
- Well-documented code (PHPDoc, inline comments)
- Modular architecture for independent component updates
- Version control (Git) with branching strategy
- CI/CD pipeline for automated testing and deployment
- AI prompt version control and management

---

## 8. Database Design

### 8.1 Entity-Relationship Overview

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  cycles         │─────│  vouchers       │─────│  applicants     │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                                                          │
                                                          ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  documents      │─────│  applications   │─────│ eligibility     │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │ verification    │
                        │ _codes          │
                        └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │ appointments    │
                        └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │ screening_      │
                        │ results         │
                        └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │ final_decisions │
                        └─────────────────┘

┌─────────────────┐     ┌─────────────────┐
│ administrators  │─────│ audit_logs      │
└─────────────────┘     └─────────────────┘

┌─────────────────┐     ┌─────────────────┐
│ ai_predictions  │     │ chatbot_        │
│                 │     │ conversations   │
└─────────────────┘     └─────────────────┘

┌─────────────────┐     ┌─────────────────┐
│ ai_prompt_logs  │     │ notifications   │
└─────────────────┘     └─────────────────┘
```

### 8.2 Core Tables (MySQL Compatible)

```sql
-- Table: cycles
CREATE TABLE `cycles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `cycle_code` VARCHAR(20) UNIQUE NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `application_deadline` DATETIME NOT NULL,
  `total_vacancies` INT UNSIGNED NOT NULL,
  `requirements` JSON NOT NULL, -- stores age range, height thresholds, etc.
  `ai_enabled` BOOLEAN DEFAULT FALSE,
  `status` ENUM('draft','active','closed','archived') DEFAULT 'draft',
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: vouchers
CREATE TABLE `vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cycle_id` INT UNSIGNED NOT NULL,
  `serial_number` VARCHAR(20) UNIQUE NOT NULL,
  `pin_code` VARCHAR(20) NOT NULL,
  `purchased_at` TIMESTAMP NULL,
  `used_by` INT UNSIGNED NULL,
  `used_at` TIMESTAMP NULL,
  `status` ENUM('available','used','expired') DEFAULT 'available',
  `expires_at` TIMESTAMP NULL,
  FOREIGN KEY (`cycle_id`) REFERENCES `cycles`(`id`)
);

-- Table: applicants
CREATE TABLE `applicants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `voucher_id` INT UNSIGNED UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `other_names` VARCHAR(50) NULL,
  `date_of_birth` DATE NOT NULL,
  `gender` ENUM('Male','Female') NOT NULL,
  `marital_status` ENUM('Single','Married','Divorced','Widowed') DEFAULT 'Single',
  `contact_number` VARCHAR(15) NOT NULL,
  `alternative_contact` VARCHAR(15) NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `residential_address` TEXT NOT NULL,
  `region` VARCHAR(50) NOT NULL,
  `district` VARCHAR(50) NOT NULL,
  `nationality` VARCHAR(50) DEFAULT 'Ghanaian',
  `national_id` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email_verified` BOOLEAN DEFAULT FALSE,
  `phone_verified` BOOLEAN DEFAULT FALSE,
  `status` ENUM('active','suspended','inactive') DEFAULT 'active',
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`)
);

-- Table: applications
CREATE TABLE `applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT UNSIGNED NOT NULL,
  `cycle_id` INT UNSIGNED NOT NULL,
  `gaf_id` VARCHAR(20) UNIQUE NOT NULL,
  `application_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `education_level` VARCHAR(50) NOT NULL,
  `institution_name` VARCHAR(200) NOT NULL,
  `qualification` VARCHAR(100) NOT NULL,
  `year_obtained` YEAR NOT NULL,
  `certificate_number` VARCHAR(50) NULL,
  `height` DECIMAL(5,2) NOT NULL,
  `weight` DECIMAL(5,2) NULL,
  `health_conditions` JSON NULL,
  `criminal_record` BOOLEAN DEFAULT FALSE,
  `fitness_status` ENUM('Excellent','Good','Average','Poor') NULL,
  `status` ENUM('draft','submitted','under_review','completed') DEFAULT 'draft',
  `submitted_at` TIMESTAMP NULL,
  `ai_eligibility_score` DECIMAL(5,2) NULL,
  `ai_ranking_score` DECIMAL(5,2) NULL,
  `ai_verified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`),
  FOREIGN KEY (`cycle_id`) REFERENCES `cycles`(`id`)
);

-- Table: documents
CREATE TABLE `documents` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `document_type` ENUM('birth_certificate','educational_cert','national_id','passport_photo','wassce_cert','degree_cert') NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(50) NOT NULL,
  `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `verification_status` ENUM('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` INT UNSIGNED NULL,
  `verified_at` TIMESTAMP NULL,
  `fraud_risk_score` INT UNSIGNED NULL,
  `fraud_flags` JSON NULL,
  `ai_verified` BOOLEAN DEFAULT FALSE,
  `ai_extracted_data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: eligibility_results
CREATE TABLE `eligibility_results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `age_check` BOOLEAN,
  `nationality_check` BOOLEAN,
  `education_check` BOOLEAN,
  `height_check` BOOLEAN,
  `criminal_check` BOOLEAN,
  `document_check` BOOLEAN,
  `marital_check` BOOLEAN,
  `overall_status` ENUM('eligible','not_eligible') NOT NULL,
  `rejection_reasons` JSON NULL,
  `ai_confidence` DECIMAL(5,2) NULL,
  `ai_explanation` TEXT NULL,
  `evaluation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: verification_codes
CREATE TABLE `verification_codes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `code_value` VARCHAR(20) UNIQUE NOT NULL,
  `qr_code_path` VARCHAR(255) NULL,
  `issue_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` TIMESTAMP NOT NULL,
  `used_status` BOOLEAN DEFAULT FALSE,
  `used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: appointments
CREATE TABLE `appointments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `scheduled_date` DATE NOT NULL,
  `scheduled_time` TIME NOT NULL,
  `venue` VARCHAR(200) NOT NULL,
  `slot_number` INT UNSIGNED NOT NULL,
  `status` ENUM('scheduled','confirmed','attended','missed','rescheduled') DEFAULT 'scheduled',
  `notification_sent` BOOLEAN DEFAULT FALSE,
  `checked_in_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: screening_results
CREATE TABLE `screening_results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `medical_result` ENUM('pass','fail','pending') DEFAULT 'pending',
  `medical_notes` TEXT NULL,
  `fitness_result` ENUM('pass','fail','pending') DEFAULT 'pending',
  `fitness_score` INT NULL,
  `interview_result` ENUM('pass','fail','pending') DEFAULT 'pending',
  `interview_notes` TEXT NULL,
  `overall_status` ENUM('pass','fail','pending') DEFAULT 'pending',
  `conducted_by` INT UNSIGNED NULL,
  `conducted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: final_decisions
CREATE TABLE `final_decisions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `decision` ENUM('admitted','deferred','rejected') NOT NULL,
  `decision_reason` TEXT NULL,
  `committee_members` JSON NULL,
  `decision_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notification_sent` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`)
);

-- Table: administrators
CREATE TABLE `administrators` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','super_admin') DEFAULT 'admin',
  `permissions` JSON NULL,
  `subscription_tier` ENUM('basic','pro','enterprise') DEFAULT 'basic',
  `subscription_expires_at` TIMESTAMP NULL,
  `ai_usage_limit` INT NULL,
  `status` ENUM('active','suspended') DEFAULT 'active',
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: audit_logs
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `user_type` ENUM('applicant','admin','screening_officer','medical_officer','interview_officer') NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: notifications
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT UNSIGNED NULL,
  `admin_id` INT UNSIGNED NULL,
  `type` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `channel` ENUM('email','sms','dashboard') NOT NULL,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL
);

-- Table: ai_predictions
CREATE TABLE `ai_predictions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cycle_id` INT UNSIGNED NOT NULL,
  `prediction_type` VARCHAR(50) NOT NULL, -- 'volume', 'success_rate', 'bottleneck'
  `predicted_value` JSON NOT NULL,
  `confidence` DECIMAL(5,2) NULL,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cycle_id`) REFERENCES `cycles`(`id`)
);

-- Table: chatbot_conversations
CREATE TABLE `chatbot_conversations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `applicant_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `ai_model` VARCHAR(50) NULL,
  `tokens_used` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: ai_prompt_logs
CREATE TABLE `ai_prompt_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `user_type` VARCHAR(20) NULL,
  `prompt_type` VARCHAR(50) NOT NULL,
  `prompt` TEXT NOT NULL,
  `response` TEXT NULL,
  `model` VARCHAR(50) NULL,
  `tokens_used` INT NULL,
  `cost` DECIMAL(10,6) NULL,
  `processing_time` DECIMAL(10,3) NULL,
  `status` ENUM('success','error','fallback') DEFAULT 'success',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: ai_prompts
CREATE TABLE `ai_prompts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `system_prompt` TEXT NOT NULL,
  `user_prompt_template` TEXT NOT NULL,
  `version` INT DEFAULT 1,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: ai_usage
CREATE TABLE `ai_usage` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `total_tokens` INT DEFAULT 0,
  `total_cost` DECIMAL(10,4) DEFAULT 0.0000,
  `requests_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `administrators`(`id`)
);
```

---

## 9. API Specifications

### 9.1 API Design Principles
- RESTful architecture
- JSON request/response format
- JWT authentication (Laravel Sanctum)
- Versioned endpoints (`/api/v1/...`)
- Consistent error responses
- Rate limiting per endpoint

### 9.2 Core API Endpoints

#### Public Endpoints (No Auth)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/cycles/active` | GET | Get active recruitment cycles |
| `/api/v1/cycles/{id}/requirements` | GET | Get cycle requirements |
| `/api/v1/announcements` | GET | Get recruitment announcements |
| `/api/v1/eligibility/pre-check` | POST | Pre-eligibility check (no auth) |
| `/api/v1/chatbot/message` | POST | Public chatbot (free tier limited) |

#### Authentication Endpoints
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/auth/register` | POST | Register with voucher |
| `/api/v1/auth/verify-email` | POST | Verify email with code |
| `/api/v1/auth/verify-phone` | POST | Verify phone with code |
| `/api/v1/auth/login` | POST | Login |
| `/api/v1/auth/logout` | POST | Logout |
| `/api/v1/auth/refresh` | POST | Refresh token |
| `/api/v1/auth/password/reset` | POST | Request password reset |
| `/api/v1/auth/password/reset/confirm` | POST | Confirm password reset |

#### Applicant Endpoints (Authenticated)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/applicant/profile` | GET | Get own profile |
| `/api/v1/applicant/profile` | PUT | Update profile |
| `/api/v1/applicant/application` | GET | Get current application |
| `/api/v1/applicant/application` | POST | Save/update application |
| `/api/v1/applicant/application/submit` | POST | Submit final application |
| `/api/v1/applicant/documents` | GET | Get documents |
| `/api/v1/applicant/documents` | POST | Upload document |
| `/api/v1/applicant/documents/{id}` | DELETE | Delete document |
| `/api/v1/applicant/status` | GET | Get application status |
| `/api/v1/applicant/verification-code` | GET | Get verification code |
| `/api/v1/applicant/appointment` | GET | Get appointment details |
| `/api/v1/applicant/notifications` | GET | Get notifications |
| `/api/v1/applicant/notifications/{id}/read` | PUT | Mark notification read |
| `/api/v1/applicant/chatbot` | POST | AI chatbot (if premium) |

#### Premium AI Endpoints (Authenticated, Subscription Required)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/ai/eligibility/analyze` | POST | AI eligibility analysis |
| `/api/v1/ai/documents/verify` | POST | Document fraud check |
| `/api/v1/ai/ranking/list` | GET | AI-ranked candidate list |
| `/api/v1/ai/chatbot` | POST | Advanced chatbot |
| `/api/v1/ai/insights` | GET | Predictive analytics summary |
| `/api/v1/ai/report/generate` | POST | Natural language report generation |
| `/api/v1/ai/usage` | GET | AI usage and cost tracking (Admin) |

#### Admin Endpoints (Admin/Super Admin)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/admin/dashboard/stats` | GET | Dashboard statistics |
| `/api/v1/admin/applications` | GET | List applications |
| `/api/v1/admin/applications/{id}` | GET | Application details |
| `/api/v1/admin/applications/{id}/status` | PUT | Update status |
| `/api/v1/admin/applications/shortlist` | POST | Bulk shortlist |
| `/api/v1/admin/documents/{id}/verify` | PUT | Verify document |
| `/api/v1/admin/scheduling/slots` | GET | List slots |
| `/api/v1/admin/scheduling/slots` | POST | Create slots |
| `/api/v1/admin/scheduling/appointments` | GET | List appointments |
| `/api/v1/admin/screening/results` | POST | Record screening results |
| `/api/v1/admin/selection/finalize` | POST | Finalize selection |
| `/api/v1/admin/reports/export` | GET | Export reports |
| `/api/v1/admin/cycles` | CRUD | Manage cycles |
| `/api/v1/admin/users` | CRUD | Manage administrators |
| `/api/v1/admin/ai/config` | PUT | AI configuration (Super Admin) |
| `/api/v1/admin/ai/usage` | GET | AI usage dashboard (Super Admin) |
| `/api/v1/admin/subscription` | GET | Subscription status |
| `/api/v1/admin/subscription/upgrade` | POST | Upgrade subscription |

### 9.3 Example API Responses

#### Successful Response
```json
{
  "success": true,
  "data": {
    "id": 12345,
    "status": "eligible",
    "message": "Application successfully submitted"
  },
  "meta": {
    "timestamp": "2026-06-24T10:30:00Z"
  }
}
```

#### AI Response Example
```json
{
  "success": true,
  "data": {
    "eligibility": "eligible",
    "confidence": 92.5,
    "explanation": "Applicant meets all criteria. Age: 22, Height: 1.72m, Education: WASSCE with 8 credits including English, Maths, and Science. No criminal record. All documents verified.",
    "checks": {
      "age": true,
      "nationality": true,
      "education": true,
      "height": true,
      "criminal": true,
      "documents": true,
      "marital": true
    }
  },
  "meta": {
    "ai_used": true,
    "model": "gpt-4-turbo",
    "processing_time": "2.3s",
    "tokens_used": 450
  }
}
```

#### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The provided data failed validation",
    "details": {
      "email": ["The email field is required"],
      "date_of_birth": ["The date of birth must be a valid date"]
    }
  },
  "meta": {
    "timestamp": "2026-06-24T10:30:00Z"
  }
}
```

---

## 10. UI/UX Design Guidelines

### 10.1 Brand Identity & Visual Language

#### Primary Color Palette
Based on Ghana Armed Forces branding:

| Color | Hex Code | Usage |
|-------|----------|-------|
| **GAF Red** | `#C8102E` | Primary CTAs, headers, accent elements |
| **GAF Dark Blue** | `#003087` | Navigation, primary backgrounds |
| **GAF Light Blue** | `#0072B0` | Secondary elements, hover states |
| **Gold** | `#FFD700` | Highlights, badges, success indicators |
| **Black** | `#000000` | Text, footer backgrounds |
| **White** | `#FFFFFF` | Backgrounds, text on dark |
| **Dartmouth Green** | `#006747` | Status indicators, military theme |
| **Military Gray** | `#8B8B8B` | Secondary text, borders |

#### Typography
- **Primary Font**: Inter (Google Fonts) – Modern, clean, highly readable
- **Heading Font**: Montserrat – Bold, authoritative, military aesthetic
- **Monospace**: JetBrains Mono – For code blocks, system identifiers

#### Design Principles
1. **Authority & Trust**: Clean, structured layouts with military-inspired design elements
2. **Simplicity**: Forms and navigation designed for users with limited digital skills
3. **Responsiveness**: Mobile-first design; works on smartphones, tablets, and computers
4. **Feedback**: Immediate visual confirmation for all user actions
5. **Accessibility**: WCAG 2.1 AA compliance

### 10.2 Key UI Components

#### Landing Page
- **Hero Section**: Full-width with GAF imagery, animated CTA button, recruitment countdown timer
- **Eligibility Checker**: Quick tool to check basic eligibility before registration
- **Recruitment Statistics**: Live counter showing total applicants, shortlisted, etc.
- **AI Chatbot Widget**: Floating widget for applicant assistance (Premium)
- **News/Updates**: Scrollable feed of recruitment announcements
- **Footer**: GAF branding, contact information, disclaimers

#### Applicant Dashboard
- **Status Timeline**: Visual progress indicator showing recruitment stage
- **Profile Card**: Applicant photo, name, GAF ID, current status
- **Quick Actions**: Resume application, upload documents, view appointment
- **Notifications**: Bell icon with real-time update indicator
- **AI Assistant**: Chatbot widget for help (Premium)

#### Application Form
- **Multi-step Wizard**: Progress bar showing completion percentage
- **Sectioned Layout**: Personal Information → Educational → Physical/Health → Document Upload → Review
- **Inline Validation**: Real-time field validation with visual feedback
- **Save Draft**: Auto-save functionality
- **AI Eligibility Advisor**: Confidence score and recommendations (Premium)

#### Admin Dashboard
- **KPI Cards**: Total applicants, eligible, shortlisted, screened, selected
- **Real-time Charts**: Regional distribution, gender distribution, stage funnel
- **AI Insights Panel**: Natural language insights and recommendations (Premium)
- **Recruitment Health Score**: Overall process health metric (Premium)
- **Quick Filters**: Search, filter by status, date range, region
- **Bulk Actions**: Batch status updates, bulk notifications

### 10.3 Animation & Micro-interactions

| Element | Animation | Purpose |
|---------|-----------|---------|
| Page transitions | Fade + slide | Smooth navigation |
| Form submissions | Loading spinner + success checkmark | User feedback |
| Status updates | Pulse + color transition | Draw attention to changes |
| Dashboard cards | Count-up animation | Engagement |
| Notifications | Slide-in from right | Non-intrusive alerts |
| Eligibility result | Reveal animation with status color | Dramatic, clear outcome |
| AI loading | Pulsing dots with "Analyzing..." text | AI processing feedback |
| KPI updates | Smooth transition between values | Real-time feel |

---

## 11. Subscription & Premium Model

### 11.1 Subscription Plans

| Plan | Monthly Fee (USD) | Included Features |
|------|-------------------|-------------------|
| **Basic** | Free | Core recruitment management (no AI) |
| **Pro** | $29/month | AI Eligibility Advisor + Document Fraud Detection + Applicant Chatbot |
| **Enterprise** | $99/month | All AI features + Bulk SMS + Priority Support + Custom AI Training |

### 11.2 Feature Breakdown

| Feature | Basic | Pro | Enterprise |
|---------|-------|-----|------------|
| Voucher registration & application | ✅ | ✅ | ✅ |
| Rule-based eligibility | ✅ | ✅ | ✅ |
| Document upload & basic validation | ✅ | ✅ | ✅ |
| Email notifications | ✅ | ✅ | ✅ |
| Basic admin dashboard with charts | ✅ | ✅ | ✅ |
| SMS notifications | ❌ | ❌ | ✅ |
| **AI Document Text Extraction** | ❌ | ✅ | ✅ |
| **AI Eligibility Assistant** | ❌ | ✅ | ✅ |
| **Document Fraud Detection** | ❌ | ✅ | ✅ |
| **AI Candidate Ranking** | ❌ | ✅ | ✅ |
| **Intelligent Applicant Chatbot** | ❌ | ✅ | ✅ |
| **Predictive Analytics** | ❌ | ❌ | ✅ |
| **AI Report Generation** | ❌ | ❌ | ✅ |
| **Bulk SMS Campaigns** | ❌ | ❌ | ✅ |
| **Priority Support** | ❌ | ❌ | ✅ |
| **Custom AI Training** | ❌ | ❌ | ✅ |

### 11.3 Implementation
- Store subscription status in `administrators` table (`subscription_tier`, `subscription_expires_at`)
- Use middleware to check subscription before allowing AI endpoints
- Payment integration (optional): Stripe/PayPal

### 11.4 Fallback Strategy
- When AI endpoints are called without a valid subscription → 403 error with upgrade message
- Or automatically fall back to rule-based processing for eligibility
- Provide clear upgrade prompts in the UI

---

## 12. Security & Compliance

### 12.1 Security Measures
- **CSRF Protection**: Laravel's built-in
- **XSS Prevention**: Blade escaping; Content Security Policy headers
- **SQL Injection**: Eloquent ORM uses parameterized queries
- **Authentication**: Sanctum tokens with expiry (configurable)
- **File Upload**: Validate mime type, scan for viruses, store outside public root
- **Role-Based Access**: Middleware `admin`, `super_admin`, `screening_officer`, etc.
- **Data Encryption**: TLS 1.3 for transit; AES-256 for stored documents
- **AI Data Protection**: Anonymization before sending to OpenAI; zero-retention policy

### 12.2 Compliance
- **Ghana Data Protection Act, 2012 (Act 843)**: Collect minimal data, get consent, allow data access/erasure
- **Recruitment Integrity**: Display disclaimers (no middlemen, forged documents lead to prosecution)
- **Access Logs**: All admin actions logged in `audit_logs`
- **AI Compliance**: Clear disclosure of AI usage; human oversight for AI decisions

### 12.3 Disclaimer Statements
- "There are no authorized middlemen and the advertised vacancies are not for sale"
- "Any person found posing as an agent or middleman will be prosecuted"
- "Anybody who submits false documents will also face arrest and prosecution"
- "AI-assisted eligibility verification is advisory; final decisions are made by recruitment officers"

---

## 13. Testing Strategy

### 13.1 Test Types

| Test Type | Tools | Coverage Target |
|-----------|-------|-----------------|
| **Unit Tests** | PHPUnit, Pest | 80%+ of business logic |
| **Feature Tests** | Laravel Dusk (Browser) | All critical user journeys |
| **API Tests** | PHPUnit with JWT | All endpoints |
| **AI Integration Tests** | Mock OpenAI responses | All AI endpoints |
| **Performance Tests** | k6 (local) | Simulate 1000 concurrent users |
| **Security Tests** | OWASP ZAP | Check for common vulnerabilities |
| **Accessibility Tests** | axe-core, WAVE | WCAG 2.1 AA |

### 13.2 Critical Test Scenarios
1. **Voucher Validation**: Invalid, used, expired vouchers rejected
2. **Document Upload**: File type, size, and completeness validation
3. **Eligibility Screening**: All criteria correctly evaluated
4. **Verification Code**: Unique, time-bound, single-use
5. **Appointment Scheduling**: No double-booking, conflict resolution
6. **Notification Delivery**: SMS and email reliably sent
7. **Authentication**: Unauthorized access prevented
8. **AI Fallback**: System degrades gracefully when AI unavailable
9. **Data Integrity**: No data loss during concurrent operations
10. **Fraud Detection**: Duplicate and suspicious applications flagged

---

## 14. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-4)
- Set up development environment (XAMPP + Laravel)
- Database schema creation and migration
- Authentication system (registration, login, password reset)
- Role-based access control
- Basic admin panel structure

### Phase 2: Core Applicant Features (Weeks 5-8)
- Applicant registration with voucher validation
- Multi-step application form
- Document upload with validation
- Application submission
- Application status tracking

### Phase 3: Eligibility & Shortlisting (Weeks 9-12)
- Rule-based eligibility algorithm
- Automated eligibility screening
- Verification code generation
- Shortlisting system
- Administrator review interface

### Phase 4: AI Integration (Weeks 13-16)
- OpenAI API setup and configuration
- AI Gateway service
- Eligibility Advisor
- Document Analysis
- Fraud Detection
- Applicant Chatbot
- AI Prompt Management

### Phase 5: Scheduling & Screening (Weeks 17-20)
- Appointment scheduling system
- SMS/Email notifications
- Screening management
- Results recording
- Final selection workflow

### Phase 6: Admin & Reporting (Weeks 21-24)
- Admin dashboard with KPI cards
- Recruitment cycle management
- Report generation (PDF/Excel)
- AI analytics dashboard
- Subscription management
- User management
- Audit logging

### Phase 7: Testing & Deployment (Weeks 25-28)
- Unit testing
- Integration testing
- AI integration testing
- User acceptance testing
- Security audit
- Performance testing
- Production deployment
- Documentation

---

## 15. Deployment Instructions

### 15.1 Local Development (XAMPP)

1. **Install XAMPP** with PHP 8.2, MySQL, Apache
2. **Clone/Download** the Laravel project to `C:\xampp\htdocs\dmrms`
3. **Create `.env`** file:
   ```env
   APP_NAME=DMRMS
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost/dmrms

   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=dmrms
   DB_USERNAME=dmrms
   DB_PASSWORD=dmrms2026

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_user
   MAIL_PASSWORD=your_mailtrap_pass
   MAIL_ENCRYPTION=tls

   # AI Configuration
   OPENAI_API_KEY=sk-...
   OPENAI_MODEL=gpt-4-turbo
   OPENAI_EMBEDDING_MODEL=text-embedding-3-small
   OPENAI_MOCK=true

   AI_ENABLED=true
   AI_FALLBACK_ENABLED=true
   SUBSCRIPTION_ENABLED=false
   ```
4. **Run `composer install`** from the project root
5. **Run `php artisan key:generate`**
6. **Run migrations**: `php artisan migrate`
7. **Seed sample data**: `php artisan db:seed`
8. **Start queues**: `php artisan queue:work` (in separate terminal)
9. **Access** via `http://localhost/dmrms/public`

### 15.2 Python AI Setup
1. Install Python 3.9+ 
2. Install packages: `pip install openai pillow requests fastapi uvicorn`
3. Create Python FastAPI microservice for AI tasks
4. Ensure PHP can execute Python scripts (`exec()` enabled in `php.ini`)

### 15.3 Production Deployment
- **Server**: VPS with LAMP stack
- **Environment**: Set `APP_ENV=production`, `APP_DEBUG=false`
- **Use HTTPS** with Let's Encrypt
- **Set up cron** for scheduler: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
- **Configure Supervisor** for queue workers
- **Set up PostgreSQL** for production database
- **Configure AI** with real API keys

---

## 16. Maintenance & Support

### 16.1 Post-Launch Support Plan

| Activity | Frequency | Responsibility |
|----------|-----------|----------------|
| **Security patches** | As needed | DevOps |
| **Bug fixes** | Within 48 hours | Development team |
| **Performance monitoring** | Daily | DevOps |
| **Database backups** | Daily | DevOps |
| **User support** | Business hours | Support team |
| **Feature updates** | Quarterly | Product team |
| **AI model updates** | As needed | AI team |
| **Prompt optimization** | Monthly | AI team |

### 16.2 Backup Strategy
- **Database**: Daily full backup + WAL archiving (point-in-time recovery)
- **Documents**: Versioned storage with replication
- **Configuration**: Infrastructure as Code (IaC) templates
- **AI Prompts**: Versioned in database with audit trail
- **Retention**: 30 days daily, 12 months monthly

---

## 17. Appendix

### A. Glossary of Terms

| Term | Definition |
|------|------------|
| **GAF** | Ghana Armed Forces |
| **DMRMS** | Digital Military Recruitment Management System |
| **WASSCE** | West African Senior School Certificate Examination |
| **SSCE** | Senior Secondary School Certificate Examination |
| **BECE** | Basic Education Certificate Examination |
| **NVTI** | National Vocational Training Institute |
| **HND** | Higher National Diploma |
| **JWT** | JSON Web Token |
| **RBAC** | Role-Based Access Control |
| **KPI** | Key Performance Indicator |
| **OCR** | Optical Character Recognition |
| **AI** | Artificial Intelligence |
| **LLM** | Large Language Model |

### B. References
1. Ghana Armed Forces Official Recruitment Portal: https://apply.mil.gh
2. Ghana Armed Forces Recruitment Guide
3. Ghana Data Protection Act, 2012 (Act 843)
4. OpenAI API Documentation: https://platform.openai.com/docs
5. Laravel Documentation: https://laravel.com/docs
6. Ghana Card Verification API: https://nia.gov.gh

### C. Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | June 2024 | DMRMS Team | Initial release |
| 2.0 | June 2025 | DMRMS Team | Added modular architecture |
| 3.0 | June 2026 | DMRMS Team | Added OpenAI integration |
| 4.0 | June 2026 | DMRMS Team | Complete enterprise edition |

---

## 18. Final Recommendations

### 18.1 Architecture Summary
The DMRMS is built as a **modular, AI-powered enterprise recruitment platform** with:
- **Laravel** as the core application framework
- **PostgreSQL** for robust data management
- **OpenAI API** for intelligent automation
- **Python FastAPI** for AI microservices
- **XAMPP** for local development

### 18.2 Key Success Factors
1. **User-Centric Design**: Prioritize simplicity for users with limited digital skills
2. **Mobile Responsiveness**: Ensure seamless experience on smartphones
3. **Security First**: Implement all security measures from day one
4. **AI Integration**: Leverage AI for intelligence while maintaining human oversight
5. **Performance**: Optimize for high concurrent user load during peak periods
6. **Testing**: Comprehensive testing across all user journeys
7. **Documentation**: Maintain thorough technical and user documentation
8. **Modularity**: Design for easy feature addition and provider switching

### 18.3 System Positioning
DMRMS is no longer simply a **Digital Military Recruitment Management System**.

It becomes:
> **AI-Powered Military Recruitment and Applicant Lifecycle Management Platform**

A complete enterprise recruitment ecosystem combining:
- Recruitment Automation
- Applicant Lifecycle Management
- Workflow Orchestration
- AI Intelligence
- Fraud Detection
- Analytics
- Auditability
- Decision Support
- Future Government Integration

---

*This Product Requirements Document serves as the authoritative specification for the development of the Digital Military Recruitment Management System. All development activities should reference this document for functional, technical, and design requirements.*