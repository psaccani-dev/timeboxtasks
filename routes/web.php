<?php

use App\Http\Controllers\{TaskController, TimeBoxController};
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

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



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
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
