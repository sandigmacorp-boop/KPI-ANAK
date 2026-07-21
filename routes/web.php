<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeSettingController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChildrenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyGoalController;
use App\Http\Controllers\KidController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

// Autentikasi orang tua
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:30,1')->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Mode anak — tanpa login, lewat link rahasia per anak
Route::get('/c/{token}/manifest.webmanifest', [KidController::class, 'manifest'])->name('kid.manifest');
Route::get('/c/{token}', [KidController::class, 'show'])->name('kid.show');
Route::get('/c/{token}/performa', [KidController::class, 'performa'])->name('kid.performa');
Route::get('/c/{token}/pengingat', [KidController::class, 'reminder'])
    ->middleware('throttle:120,1')->name('kid.reminder');
Route::post('/c/{token}/toggle/{task}', [KidController::class, 'toggle'])
    ->middleware('throttle:60,1')->name('kid.toggle');
Route::post('/c/{token}/mood', [KidController::class, 'setMood'])
    ->middleware('throttle:60,1')->name('kid.mood');
Route::post('/c/{token}/tukar/{reward}', [KidController::class, 'redeem'])
    ->middleware('throttle:20,1')->name('kid.redeem');

// Area orang tua
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('/anak/{child}/checklist/{date?}', [ChecklistController::class, 'show'])
        ->where('date', '\d{4}-\d{2}-\d{2}')->name('checklist');
    Route::post('/anak/{child}/toggle/{task}', [ChecklistController::class, 'toggle'])->name('checklist.toggle');
    Route::post('/anak/{child}/mood', [ChecklistController::class, 'setMood'])->name('checklist.mood');
    Route::get('/anak/{child}/laporan', [ReportController::class, 'show'])->name('report');

    Route::post('/kelola/tantangan', [ChallengeSettingController::class, 'store'])->name('challenge.store');
    Route::delete('/kelola/tantangan', [ChallengeSettingController::class, 'reset'])->name('challenge.reset');

    Route::post('/kelola/tujuan', [FamilyGoalController::class, 'store'])->name('goals.store');
    Route::post('/kelola/tujuan/{goal}/selesai', [FamilyGoalController::class, 'claim'])->name('goals.claim');
    Route::delete('/kelola/tujuan/{goal}', [FamilyGoalController::class, 'destroy'])->name('goals.destroy');

    Route::get('/kelola', [ChildrenController::class, 'index'])->name('children.index');
    Route::post('/kelola/anak', [ChildrenController::class, 'store'])->name('children.store');
    Route::put('/kelola/anak/{child}', [ChildrenController::class, 'update'])->name('children.update');
    Route::delete('/kelola/anak/{child}', [ChildrenController::class, 'destroy'])->name('children.destroy');
    Route::post('/kelola/anak/{child}/token', [ChildrenController::class, 'newToken'])->name('children.token');

    Route::get('/kelola/anak/{child}/hadiah', [RewardsController::class, 'index'])->name('rewards.index');
    Route::post('/kelola/anak/{child}/hadiah', [RewardsController::class, 'store'])->name('rewards.store');
    Route::put('/kelola/hadiah/{reward}', [RewardsController::class, 'update'])->name('rewards.update');
    Route::delete('/kelola/hadiah/{reward}', [RewardsController::class, 'destroy'])->name('rewards.destroy');
    Route::post('/kelola/hadiah/{reward}/aktif', [RewardsController::class, 'toggleActive'])->name('rewards.active');
    Route::post('/kelola/tukar/{redemption}/berikan', [RewardsController::class, 'deliver'])->name('redemptions.deliver');
    Route::post('/kelola/tukar/{redemption}/batal', [RewardsController::class, 'cancel'])->name('redemptions.cancel');

    Route::get('/kelola/anak/{child}/poin', [PointsController::class, 'index'])->name('points.index');
    Route::post('/kelola/anak/{child}/poin', [PointsController::class, 'store'])->name('points.store');
    Route::delete('/kelola/poin/{adjustment}', [PointsController::class, 'destroy'])->name('points.destroy');

    Route::get('/kelola/anak/{child}/tugas', [TasksController::class, 'index'])->name('tasks.index');
    Route::post('/kelola/anak/{child}/tugas', [TasksController::class, 'store'])->name('tasks.store');
    Route::put('/kelola/tugas/{task}', [TasksController::class, 'update'])->name('tasks.update');
    Route::delete('/kelola/tugas/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/kelola/tugas/{task}/aktif', [TasksController::class, 'toggleActive'])->name('tasks.active');

    Route::get('/pengaturan', [SettingsController::class, 'show'])->name('settings');
    Route::post('/pengaturan/profil', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/pengaturan/sandi', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/pengaturan/ortu', [SettingsController::class, 'addParent'])->name('settings.parents.add');
    Route::delete('/pengaturan/ortu/{user}', [SettingsController::class, 'removeParent'])->name('settings.parents.remove');
    Route::post('/pengaturan/telegram/hubungkan', [SettingsController::class, 'linkTelegram'])->name('settings.telegram.link');
    Route::post('/pengaturan/telegram/tes', [SettingsController::class, 'testTelegram'])->name('settings.telegram.test');
    Route::post('/pengaturan/telegram/putus', [SettingsController::class, 'unlinkTelegram'])->name('settings.telegram.unlink');
});
