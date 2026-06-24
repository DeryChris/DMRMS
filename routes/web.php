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

// Applicant routes
Route::prefix('applicant')->name('applicant.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [ApplicantWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/application', [ApplicantWebController::class, 'applicationForm'])->name('application');
    Route::get('/documents', [ApplicantWebController::class, 'documents'])->name('documents');
    Route::get('/status', [ApplicantWebController::class, 'status'])->name('status');
    Route::get('/appointment', [ApplicantWebController::class, 'appointment'])->name('appointment');
    Route::get('/notifications', [ApplicantWebController::class, 'notifications'])->name('notifications');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,super_admin'])->group(function () {
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
});

// Screening officer routes
Route::prefix('screening')->name('screening.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [ScreeningWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/verify', [ScreeningWebController::class, 'verify'])->name('verify');
    Route::get('/medical', [ScreeningWebController::class, 'medical'])->name('medical');
    Route::get('/fitness', [ScreeningWebController::class, 'fitness'])->name('fitness');
    Route::get('/interview', [ScreeningWebController::class, 'interview'])->name('interview');
});
