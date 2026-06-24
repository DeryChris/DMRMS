<?php

use App\Http\Controllers\Web\WebController;
use App\Http\Controllers\Web\ApplicantWebController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\ScreeningWebController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [WebController::class, 'landing'])->name('landing');
Route::get('/eligibility-checker', [WebController::class, 'eligibilityChecker'])->name('eligibility.checker');
Route::get('/announcements', [WebController::class, 'announcements'])->name('announcements');
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

        // Applicant password reset
        Route::get('forgot-password', [\App\Http\Controllers\Auth\ApplicantPasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [\App\Http\Controllers\Auth\ApplicantPasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\ApplicantNewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [\App\Http\Controllers\Auth\ApplicantNewPasswordController::class, 'store'])->name('password.store');
    });

    // Applicant routes (authenticated)
    Route::middleware('auth:applicant')->group(function () {
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

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,super_admin,recruitment_officer,screening_officer,scheduling_officer'])->group(function () {
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [AdminWebController::class, 'applications'])->name('applications');
    Route::get('/applications/{id}', [AdminWebController::class, 'applicationDetail'])->name('applications.detail');
    Route::get('/cycles', [AdminWebController::class, 'cycles'])->name('cycles');
    Route::get('/scheduling', [AdminWebController::class, 'scheduling'])->name('scheduling');
    Route::get('/screening-results', [AdminWebController::class, 'screeningResults'])->name('screening-results');
    Route::get('/selection', [AdminWebController::class, 'selection'])->name('selection');
    Route::get('/reports', [AdminWebController::class, 'reports'])->name('reports');
    Route::get('/users', [AdminWebController::class, 'users'])->name('users');
    Route::get('/ai-config', [AdminWebController::class, 'aiConfig'])->name('ai-config');
    Route::get('/audit-logs', [AdminWebController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/settings', [AdminWebController::class, 'settings'])->name('settings');

    // Admin notification management
    Route::get('/notifications/send', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications/send', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'send'])->name('notifications.send');
});

// Screening officer routes
Route::prefix('screening')->name('screening.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [ScreeningWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/verify', [ScreeningWebController::class, 'verify'])->name('verify');
    Route::get('/medical', [ScreeningWebController::class, 'medical'])->name('medical');
    Route::get('/fitness', [ScreeningWebController::class, 'fitness'])->name('fitness');
    Route::get('/interview', [ScreeningWebController::class, 'interview'])->name('interview');
});
