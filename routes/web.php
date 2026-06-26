<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BarrackController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\ApplicantWebController;
use App\Http\Controllers\Web\ScreeningWebController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [WebController::class, 'landing'])->name('landing');
Route::get('/recruitment', [WebController::class, 'recruitmentPortal'])->name('recruitment.portal');
Route::get('/voucher/buy', [\App\Http\Controllers\Web\VoucherPurchaseController::class, 'showPurchaseForm'])->name('voucher.buy');
Route::post('/voucher/buy', [\App\Http\Controllers\Web\VoucherPurchaseController::class, 'purchase'])->name('voucher.purchase');
Route::get('/voucher/{voucher}/confirmation', [\App\Http\Controllers\Web\VoucherPurchaseController::class, 'confirmation'])->name('voucher.confirmation');
Route::get('/eligibility-checker', [WebController::class, 'eligibilityChecker'])->name('eligibility.checker');
Route::get('/announcements', [WebController::class, 'announcements'])->name('announcements');
Route::get('/announcements/{id}', [WebController::class, 'announcementDetail'])->name('announcements.detail');
Route::get('/news/{id}/{slug?}', [WebController::class, 'announcementDetail'])->name('news.detail');
Route::get('/guide', [WebController::class, 'guide'])->name('guide');
Route::get('/faq', [WebController::class, 'faq'])->name('faq');
Route::get('/contact', [WebController::class, 'contact'])->name('contact');

// Breeze auth routes
require __DIR__ . '/auth.php';

// Notification AJAX routes (for bell component) — works with both guards
Route::middleware('auth:applicant,web')->group(function () {
    Route::get('/notifications/fetch', [\App\Http\Controllers\NotificationWebController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationWebController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationWebController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [\App\Http\Controllers\NotificationWebController::class, 'markAllAsRead']);
    Route::get('/notifications', [\App\Http\Controllers\NotificationWebController::class, 'allNotifications'])->name('notifications.all');
});

// Applicant auth routes
Route::prefix('applicant')->name('applicant.')->group(function () {
    Route::middleware('guest:applicant')->group(function () {
        Route::get('login', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'login'])->name('login.post');
        Route::get('register', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'register']);

        // Email verification
        Route::get('verify-email', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'showVerifyForm'])->name('verify.form');
        Route::post('verify-email', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'verify'])->name('verify');
        Route::get('verify-email/resend', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'resendVerification'])->name('verify.resend');

        // Applicant password reset
        Route::get('forgot-password', [\App\Http\Controllers\Auth\ApplicantPasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [\App\Http\Controllers\Auth\ApplicantPasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\ApplicantNewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [\App\Http\Controllers\Auth\ApplicantNewPasswordController::class, 'store'])->name('password.store');
    });

    // Applicant routes (authenticated)
    Route::middleware(['auth:applicant', 'applicant.access'])->group(function () {
        Route::post('logout', [\App\Http\Controllers\Web\ApplicantAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ApplicantWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/application', [ApplicantWebController::class, 'applicationForm'])->name('application');
        Route::post('/application', [ApplicantWebController::class, 'saveApplication'])->name('application.save');
        Route::post('/application/submit', [ApplicantWebController::class, 'submitApplication'])->name('application.submit');
        Route::get('/documents', [ApplicantWebController::class, 'documents'])->name('documents');
        Route::post('/documents/upload', [ApplicantWebController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/documents/{id}', [ApplicantWebController::class, 'deleteDocument'])->name('documents.delete');
        Route::get('/status', [ApplicantWebController::class, 'status'])->name('status');
        Route::get('/appointment', [ApplicantWebController::class, 'appointment'])->name('appointment');
        Route::get('/notifications', [ApplicantWebController::class, 'notifications'])->name('notifications');
    });
});

// Profile completion (auth-only, before admin group to avoid role middleware)
Route::middleware('auth')->group(function () {
    Route::get('/admin/profile/complete', [AdminWebController::class, 'profileComplete'])->name('admin.profile.complete');
    Route::post('/admin/profile/complete', [AdminWebController::class, 'profileCompleteStore'])->name('admin.profile.complete.store');
});

// Admin routes — broad gate: any admin role
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,super_admin,recruitment_officer,screening_officer,scheduling_officer'])->group(function () {

    // Dashboard — all roles
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');

    // Applications (read) — all roles (recruitment_officer needs list + detail for doc verify)
    Route::get('/applications', [AdminWebController::class, 'applications'])->name('applications');
    Route::get('/applications/{id}', [AdminWebController::class, 'applicationDetail'])->name('applications.detail');
    Route::post('/documents/{id}/verify', [AdminWebController::class, 'verifyDocument'])->name('documents.verify');
    Route::get('/documents/{id}/view', [AdminWebController::class, 'viewDocument'])->name('documents.view');

    // Applicant account management — admin + super_admin + recruitment_officer
    Route::middleware('role:admin,super_admin,recruitment_officer')->group(function () {
        Route::get('/applicants', [AdminWebController::class, 'applicants'])->name('applicants');
        Route::put('/applicants/{applicant}', [AdminWebController::class, 'updateApplicant'])->name('applicants.update');
        Route::put('/applicants/{applicant}/status', [AdminWebController::class, 'toggleApplicantStatus'])->name('applicants.toggle-status');
        Route::delete('/applicants/{applicant}', [AdminWebController::class, 'deleteApplicant'])->name('applicants.delete');
        Route::get('/recruited', [AdminWebController::class, 'recruited'])->name('recruited');
        Route::post('/applications/{id}/send-back', [AdminWebController::class, 'sendBack'])->name('applications.send-back');
    });

    // Selection actions — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::post('/applications/{id}/recruit', [AdminWebController::class, 'markRecruited'])->name('recruit');
        Route::get('/applications/{id}/offer-letter', [AdminWebController::class, 'offerLetter'])->name('offer-letter');
    });

    // Cycles — recruitment_officer + admin + super_admin
    Route::middleware('role:admin,super_admin,recruitment_officer')->group(function () {
        Route::get('/cycles', [AdminWebController::class, 'cycles'])->name('cycles');
        Route::post('/cycles', [AdminWebController::class, 'cycleStore'])->name('cycles.store');
        Route::put('/cycles/{cycle}', [AdminWebController::class, 'cycleUpdate'])->name('cycles.update');
        Route::put('/cycles/{cycle}/publish', [AdminWebController::class, 'cyclePublish'])->name('cycles.publish');
        Route::put('/cycles/{cycle}/close', [AdminWebController::class, 'cycleClose'])->name('cycles.close');
        Route::put('/cycles/{cycle}/archive', [AdminWebController::class, 'cycleArchive'])->name('cycles.archive');
    });

    // Scheduling — scheduling_officer + admin + super_admin
    Route::middleware('role:admin,super_admin,scheduling_officer')->group(function () {
        Route::get('/scheduling', [AdminWebController::class, 'scheduling'])->name('scheduling');
        Route::post('/scheduling/create-slots', [AdminWebController::class, 'createSlots'])->name('scheduling.create-slots');
        Route::post('/scheduling/assign', [AdminWebController::class, 'assignSlot'])->name('scheduling.assign');
    });

    // Screening results — screening_officer + admin + super_admin
    Route::middleware('role:admin,super_admin,screening_officer')->group(function () {
        Route::get('/screening-results', [AdminWebController::class, 'screeningResults'])->name('screening-results');
        Route::post('/screening/verify-code', [AdminWebController::class, 'screeningVerifyCode'])->name('screening.verify-code');
        Route::post('/screening/save-medical', [AdminWebController::class, 'screeningSaveMedical'])->name('screening.save-medical');
        Route::post('/screening/save-fitness', [AdminWebController::class, 'screeningSaveFitness'])->name('screening.save-fitness');
        Route::post('/screening/save-interview', [AdminWebController::class, 'screeningSaveInterview'])->name('screening.save-interview');
    });

    // Selection — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/selection', [AdminWebController::class, 'selection'])->name('selection');
        Route::get('/selection/stats', [AdminWebController::class, 'selectionStats'])->name('selection.stats');
        Route::post('/selection/shortlist', [AdminWebController::class, 'shortlist'])->name('selection.shortlist');
        Route::post('/selection/finalize', [AdminWebController::class, 'finalizeDecision'])->name('selection.finalize');
    });

    // Dashboard stats — all admin roles
    Route::get('/dashboard/stats', [AdminWebController::class, 'dashboardStats'])->name('dashboard.stats');

    // KPIs + Reports — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/kpi', [AdminWebController::class, 'kpi'])->name('kpi');
        Route::get('/reports', [AdminWebController::class, 'reports'])->name('reports');
        Route::post('/reports/export', [AdminWebController::class, 'exportReport'])->name('reports.export');
    });

    // Settings + AI Config — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/settings', [AdminWebController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminWebController::class, 'settingsStore'])->name('settings.store');
        Route::get('/ai-config', [AdminWebController::class, 'aiConfig'])->name('ai-config');
        Route::post('/ai-config/save', [AdminWebController::class, 'aiConfigSave'])->name('ai-config.save');
    });

    // Admin notifications — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/notifications/send', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications/send', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'send'])->name('notifications.send');
    });

    // Announcements — admin + super_admin only
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [\App\Http\Controllers\Admin\AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::post('/announcements/{announcement}/toggle-publish', [\App\Http\Controllers\Admin\AnnouncementController::class, 'togglePublish'])->name('announcements.toggle-publish');
        Route::post('/announcements/unsplash-fetch', [\App\Http\Controllers\Admin\AnnouncementController::class, 'unsplashFetch'])->name('announcements.unsplash-fetch');
    });

    // Super admin only
    Route::middleware('role:super_admin')->group(function () {
        // Users CRUD
        Route::get('/users', [AdminWebController::class, 'users'])->name('users');
        Route::post('/users', [AdminWebController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminWebController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/status', [AdminWebController::class, 'toggleUserStatus'])->name('users.toggle-status');

        // Audit logs
        Route::get('/audit-logs', [AdminWebController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/audit-logs/export', [AdminWebController::class, 'exportAuditLogs'])->name('audit-logs.export');

        // Backups
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // Barracks
        Route::get('/barracks', [BarrackController::class, 'index'])->name('barracks.index');
        Route::post('/barracks', [BarrackController::class, 'store'])->name('barracks.store');
        Route::put('/barracks/{barrack}', [BarrackController::class, 'update'])->name('barracks.update');
        Route::delete('/barracks/{barrack}', [BarrackController::class, 'destroy'])->name('barracks.destroy');

        // Security
        Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
        Route::post('/security/password-policy', [SecurityController::class, 'updatePasswordPolicy'])->name('security.password-policy');
        Route::post('/security/mfa', [SecurityController::class, 'updateMfa'])->name('security.mfa');
        Route::post('/security/sessions/{session}/terminate', [SecurityController::class, 'terminateSession'])->name('security.sessions.terminate');
        Route::post('/security/ip-access', [SecurityController::class, 'updateIpAccess'])->name('security.ip-access');
    });
});

// Screening officer routes
Route::prefix('screening')->name('screening.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [ScreeningWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/verify', [ScreeningWebController::class, 'verify'])->name('verify');
    Route::post('/verify-entry', [ScreeningWebController::class, 'verifyEntry'])->name('verify-entry');
    Route::post('/checkin', [ScreeningWebController::class, 'checkin'])->name('checkin');
    Route::post('/search-applicant', [ScreeningWebController::class, 'searchApplicant'])->name('search-applicant');
    Route::get('/medical', [ScreeningWebController::class, 'medical'])->name('medical');
    Route::post('/medical', [ScreeningWebController::class, 'recordMedical'])->name('medical.store');
    Route::get('/fitness', [ScreeningWebController::class, 'fitness'])->name('fitness');
    Route::post('/fitness', [ScreeningWebController::class, 'recordFitness'])->name('fitness.store');
    Route::get('/interview', [ScreeningWebController::class, 'interview'])->name('interview');
    Route::post('/interview', [ScreeningWebController::class, 'recordInterview'])->name('interview.store');
});
