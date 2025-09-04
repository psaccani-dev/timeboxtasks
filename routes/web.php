<?php

use App\Http\Controllers\{AnalyticsController, DashboardController, SettingsController, TaskController, TimeBoxController};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');



Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->middleware(['auth'])
    ->name('analytics.index');

// Rotas protegidas por auth
Route::middleware(['auth', 'verified'])->group(function () {

    // Tasks - Resource routes
    Route::resource('tasks', TaskController::class);

    // TimeBoxes - Resource routes  
    Route::resource('time-boxes', TimeBoxController::class);
    Route::patch('/time-boxes/{timeBox}/time', [TimeBoxController::class, 'updateTime'])->name('time-boxes.update-time');
    Route::get('/calendar', [TimeBoxController::class, 'calendar'])->name('calendar.index');
    // Rotas adicionais específicas (se necessário futuramente)
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::get('tasks/{task}/time-boxes', [TaskController::class, 'timeBoxes'])->name('tasks.time-boxes');

    Route::prefix('app-settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('app-settings.index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('app-settings.profile');
        Route::post('/update', [SettingsController::class, 'updateSettings'])->name('app-settings.update');
        Route::post('/language', [SettingsController::class, 'changeLanguage'])->name('app-settings.language');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('app-settings.password');
        Route::get('/export', [SettingsController::class, 'exportData'])->name('app-settings.export');
        Route::delete('/delete-account', [SettingsController::class, 'deleteAccount'])->name('app-settings.delete-account');
    });
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
