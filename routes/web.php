<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentTransactionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/read', [DashboardController::class, 'markNotificationsRead'])
        ->name('notifications.read');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])
        ->middleware('can:manage-portal')
        ->name('courses.create');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enrollment', [EnrollmentTransactionController::class, 'cancel'])
        ->name('courses.cancel');

    Route::get('/enrollments', [EnrollmentTransactionController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/create', [EnrollmentTransactionController::class, 'create'])->name('enrollments.create');
    Route::post('/enrollments', [EnrollmentTransactionController::class, 'store'])->name('enrollments.store');
    Route::get('/enrollments/{enrollmentTransaction}', [EnrollmentTransactionController::class, 'show'])
        ->name('enrollments.show');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::prefix('/api')->name('api.')->group(function (): void {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/courses/almost-full', [DashboardController::class, 'almostFull'])->name('courses.almost-full');
        Route::get('/enrollments/recent', [DashboardController::class, 'recentEnrollments'])->name('enrollments.recent');
    });

    Route::middleware('can:manage-portal')->group(function (): void {
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/adjust-slots', [CourseController::class, 'adjust'])->name('courses.adjust');
        Route::delete('/enrollments/{enrollmentTransaction}', [EnrollmentTransactionController::class, 'destroy'])
            ->name('enrollments.destroy');
    });
});
