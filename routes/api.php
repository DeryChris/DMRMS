<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PublicController;
use App\Http\Controllers\Api\V1\ApplicantController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\ScreeningController;
use App\Http\Controllers\Api\V1\AiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Public endpoints (no auth)
    Route::get('cycles/active', [PublicController::class, 'activeCycles'])->name('cycles.active');
    Route::get('cycles/{id}/requirements', [PublicController::class, 'cycleRequirements'])->name('cycles.requirements');
    Route::get('announcements', [PublicController::class, 'announcements'])->name('announcements');
    Route::post('eligibility/pre-check', [PublicController::class, 'preEligibilityCheck'])->name('eligibility.pre-check');
    Route::post('chatbot/message', [PublicController::class, 'chatbotMessage'])->name('chatbot.message');

    // Authentication
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');
    Route::post('auth/verify-phone', [AuthController::class, 'verifyPhone'])->name('auth.verify-phone');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
    Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('auth:sanctum');
    Route::post('auth/password/reset', [AuthController::class, 'forgotPassword'])->name('auth.password.reset');
    Route::post('auth/password/reset/confirm', [AuthController::class, 'resetPassword'])->name('auth.password.reset-confirm');

    // Authenticated applicant endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('applicant')->name('applicant.')->group(function () {
            Route::get('profile', [ApplicantController::class, 'profile'])->name('profile');
            Route::put('profile', [ApplicantController::class, 'updateProfile'])->name('profile.update');
            Route::get('application', [ApplicantController::class, 'application'])->name('application');
            Route::post('application', [ApplicantController::class, 'saveApplication'])->name('application.save');
            Route::post('application/submit', [ApplicantController::class, 'submitApplication'])->name('application.submit');
            Route::get('documents', [ApplicantController::class, 'documents'])->name('documents');
            Route::post('documents', [ApplicantController::class, 'uploadDocument'])->name('documents.upload');
            Route::delete('documents/{id}', [ApplicantController::class, 'deleteDocument'])->name('documents.delete');
            Route::get('status', [ApplicantController::class, 'status'])->name('status');
            Route::get('verification-code', [ApplicantController::class, 'verificationCode'])->name('verification-code');
            Route::get('appointment', [ApplicantController::class, 'appointment'])->name('appointment');
            Route::get('notifications', [ApplicantController::class, 'notifications'])->name('notifications');
            Route::put('notifications/{id}/read', [ApplicantController::class, 'markNotificationRead'])->name('notifications.read');
            Route::post('chatbot', [ApplicantController::class, 'chatbot'])->name('chatbot');
        });

        // Premium AI endpoints (require subscription)
        Route::prefix('ai')->name('ai.')->middleware('subscription')->group(function () {
            Route::post('eligibility/analyze', [AiController::class, 'eligibilityAnalysis'])->name('eligibility.analyze');
            Route::post('documents/verify', [AiController::class, 'documentVerification'])->name('documents.verify');
            Route::get('ranking/list', [AiController::class, 'rankingList'])->name('ranking.list');
            Route::post('chatbot', [AiController::class, 'chatbot'])->name('chatbot');
            Route::get('insights', [AiController::class, 'insights'])->name('insights');
            Route::post('report/generate', [AiController::class, 'reportGeneration'])->name('report.generate');
            Route::get('usage', [AiController::class, 'usage'])->name('usage');
        });

        // Admin endpoints
        Route::prefix('admin')->name('admin.')->middleware('role:admin,super_admin')->group(function () {
            Route::get('dashboard/stats', [AdminController::class, 'dashboardStats'])->name('dashboard.stats');
            Route::get('applications', [AdminController::class, 'applications'])->name('applications');
            Route::get('applications/{id}', [AdminController::class, 'applicationDetail'])->name('applications.detail');
            Route::put('applications/{id}/status', [AdminController::class, 'updateApplicationStatus'])->name('applications.status');
            Route::post('applications/shortlist', [AdminController::class, 'shortlist'])->name('applications.shortlist');
            Route::put('documents/{id}/verify', [AdminController::class, 'verifyDocument'])->name('documents.verify');
            Route::get('scheduling/slots', [AdminController::class, 'slots'])->name('scheduling.slots');
            Route::post('scheduling/slots', [AdminController::class, 'createSlot'])->name('scheduling.slots.create');
            Route::get('scheduling/appointments', [AdminController::class, 'appointments'])->name('scheduling.appointments');
            Route::post('screening/results', [AdminController::class, 'screeningResults'])->name('screening.results');
            Route::post('selection/finalize', [AdminController::class, 'finalizeSelection'])->name('selection.finalize');
            Route::get('reports/export', [AdminController::class, 'exportReports'])->name('reports.export');
            Route::match(['get', 'post'], 'cycles', [AdminController::class, 'cycles'])->name('cycles.index');
            Route::match(['get', 'put', 'delete'], 'cycles/{id}', [AdminController::class, 'cycles'])->name('cycles.crud');
            Route::match(['get', 'post'], 'users', [AdminController::class, 'users'])->name('users.index');
            Route::match(['get', 'put', 'delete'], 'users/{id}', [AdminController::class, 'users'])->name('users.crud');
            Route::put('ai/config', [AdminController::class, 'aiConfig'])->name('ai.config');
            Route::get('ai/usage', [AdminController::class, 'aiUsage'])->name('ai.usage');
            Route::get('subscription', [AdminController::class, 'subscription'])->name('subscription');
            Route::post('subscription/upgrade', [AdminController::class, 'upgradeSubscription'])->name('subscription.upgrade');
        });
    });

    // Screening endpoints
    Route::middleware('auth:sanctum')->prefix('screening')->name('screening.')->group(function () {
        Route::post('verify-entry', [ScreeningController::class, 'verifyEntry'])->name('verify-entry');
        Route::post('medical', [ScreeningController::class, 'recordMedical'])->name('medical');
        Route::post('fitness', [ScreeningController::class, 'recordFitness'])->name('fitness');
        Route::post('interview', [ScreeningController::class, 'recordInterview'])->name('interview');
    });
});
