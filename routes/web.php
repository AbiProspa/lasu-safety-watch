<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\ManageReportController;
use Illuminate\Support\Facades\Route;

// ---------- Public pages ----------
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/map', [PublicController::class, 'map'])->name('map');
Route::get('/map/data', [PublicController::class, 'mapData'])->name('map.data');
Route::get('/safety', [PublicController::class, 'safety'])->name('safety');
Route::get('/track', [PublicController::class, 'track'])->name('track');

// ---------- Reporting (works without login) ----------
Route::get('/report', [ReportController::class, 'create'])->name('report.create');
Route::post('/report', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('report.store');
Route::get('/report/submitted/{reference}', [ReportController::class, 'submitted'])->name('report.submitted');
Route::post('/sos', [ReportController::class, 'sos'])->middleware('throttle:5,1')->name('sos');

// ---------- Authentication ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/my-reports', [ReportController::class, 'mine'])->middleware('auth')->name('reports.mine');

// ---------- Staff (responders + admin) ----------
Route::middleware(['auth', 'role:staff'])->prefix('dashboard')->name('staff.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sos-feed', [DashboardController::class, 'sosFeed'])->name('sos-feed');
    Route::get('/reports', [ManageReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ManageReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/{report}', [ManageReportController::class, 'show'])->name('reports.show');
    Route::patch('/reports/{report}', [ManageReportController::class, 'update'])->name('reports.update');
});

// ---------- Admin only ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
});
